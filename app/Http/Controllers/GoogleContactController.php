<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Google_Client;
use Google_Service_PeopleService;
use Google_Service_PeopleService_Person;
use Google_Service_PeopleService_Name;
use Google_Service_PeopleService_PhoneNumber;

class GoogleContactController extends Controller
{
    protected function createGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_CONTACT'));
        $client->setAccessType('offline');
        $client->addScope([
            Google_Service_PeopleService::CONTACTS,
            Google_Service_PeopleService::CONTACTS_READONLY
        ]);
        return $client;
    }

    public function redirect()
    {
        $client = $this->createGoogleClient();
        return redirect($client->createAuthUrl());
    }

    public function callback(Request $request)
    {
        $code = $request->get('code');
        if (!$code) {
            return response()->json(['error' => 'Missing authorization code'], 400);
        }

        $client = $this->createGoogleClient();
        $token  = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return response()->json(['error' => 'Failed to get token', 'details' => $token], 400);
        }

        $client->setAccessToken($token);
        session(['google_token' => $token]);

        return $this->fetchAndStoreContacts($client);
    }

    protected function fetchAndStoreContacts(Google_Client $client, bool $isRefresh = false)
    {
        $service = new Google_Service_PeopleService($client);

        $allContacts = [];
        $pageToken = null;

        do {
            $response = $service->people_connections->listPeopleConnections('people/me', [
                'pageSize'     => 1000,
                'personFields' => 'names,phoneNumbers',
                'pageToken'    => $pageToken,
            ]);

            $connections = $response->getConnections() ?? [];

            foreach ($connections as $person) {
                $allContacts[] = [
                    'resourceName' => $person->getResourceName(),
                    'etag'         => $person->getEtag(),
                    'name'         => optional($person->getNames()[0] ?? null)->getDisplayName() ?? 'Unnamed',
                    'phone'        => optional($person->getPhoneNumbers()[0] ?? null)->getValue() ?? '-',
                ];
            }

            $pageToken = $response->getNextPageToken();
        } while ($pageToken);

        usort($allContacts, function ($a, $b) {
            return strcmp(strtolower($a['name']), strtolower($b['name']));
        });

        session(['google_contacts' => $allContacts]);

        return redirect()->route('google-contact.index')
            ->with('status', $isRefresh ? 'Contacts refreshed successfully.' : 'Contacts retrieved successfully.');
    }

    public function refresh()
    {
        if (!session()->has('google_token')) {
            return redirect()->route('google-contact.redirect');
        }

        $client = $this->createGoogleClient();
        $client->setAccessToken(session('google_token'));

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            session(['google_token' => $client->getAccessToken()]);
        }

        return $this->fetchAndStoreContacts($client, true);
    }

    public function logout()
    {
        session()->forget('google_token');
        session()->forget('google_contacts');
        return redirect()->route('home.index');
    }

    public function backup()
    {
        $contacts = session('google_contacts', []);

        $response = new StreamedResponse(function () use ($contacts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Name', 'Labels', 'Phone 1 - Label', 'Phone 1 - Value']);

            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact['name'],
                    '* myContacts',
                    'Mobile',
                    $contact['phone'],
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="google_contacts_backup.csv"');

        return $response;
    }

    public function index(Request $request)
    {
        if (!session()->has('google_token')) {
            return redirect()->route('google-contact.redirect');
        }

        $service = Service::where('slug', 'google-contact')->first();

        if ($service) {
            $cookieName = 'viewed_service_' . $service->id;
            if (!$request->cookie($cookieName)) {
                $service->increment('view_total');
                Cookie::queue($cookieName, true, 10);
            }
        }

        $contacts = session('google_contacts', []);
        return view('services.google-contact.index', compact('service', 'contacts'));
    }

    public function bulkUpdate(Request $request)
    {
        if (!session()->has('google_token')) {
            return redirect()->route('google-contact.redirect');
        }

        $client = $this->createGoogleClient();
        $client->setAccessToken(session('google_token'));

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            session(['google_token' => $client->getAccessToken()]);
        }

        $service = new Google_Service_PeopleService($client);

        $contactsToUpdate = $request->input('contacts');
        $originalContacts = session('google_contacts', []);
        $originalMap = collect($originalContacts)->keyBy('resourceName');

        foreach ($contactsToUpdate as $contactData) {
            $original = $originalMap[$contactData['resourceName']] ?? null;

            if (
                $original &&
                trim($original['name']) === trim($contactData['name']) &&
                trim($original['phone']) === trim($contactData['phone'])
            ) {
                continue;
            }

            $person = new Google_Service_PeopleService_Person();

            if (isset($contactData['etag'])) {
                $person->setEtag($contactData['etag']);
            }

            if (isset($contactData['name'])) {
                $name = new Google_Service_PeopleService_Name();
                $name->setGivenName($contactData['name']);
                $person->setNames([$name]);
            }

            if (isset($contactData['phone'])) {
                $phone = new Google_Service_PeopleService_PhoneNumber();
                $phone->setValue($contactData['phone']);
                $person->setPhoneNumbers([$phone]);
            }

            try {
                $service->people->updateContact(
                    $contactData['resourceName'],
                    $person,
                    ['updatePersonFields' => 'names,phoneNumbers']
                );
            } catch (\Exception $e) {
                Log::error('Gagal update kontak: ' . $e->getMessage());
                continue;
            }
        }

        $this->fetchAndStoreContacts($client, true);

        return redirect()->route('google-contact.index')->with('status', 'Kontak berhasil diperbarui.');
    }
}
