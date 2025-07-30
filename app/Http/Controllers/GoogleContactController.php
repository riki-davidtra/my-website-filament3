<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Cookie;
use Google_Client;
use Google_Service_PeopleService;

class GoogleContactController extends Controller
{
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
        return view('services.google-contact.index', compact('contacts'));
    }

    protected function createGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_CONTACT'));
        $client->addScope(Google_Service_PeopleService::CONTACTS_READONLY);
        $client->setAccessType('offline');
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
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return response()->json(['error' => 'Failed to get token', 'details' => $token], 400);
        }

        $client->setAccessToken($token);
        session(['google_token' => $token]);

        return $this->fetchAndStoreContacts($client);
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

    protected function fetchAndStoreContacts(Google_Client $client, bool $isRefresh = false)
    {
        $service = new Google_Service_PeopleService($client);

        $response = $service->people_connections->listPeopleConnections('people/me', [
            'pageSize' => 50,
            'personFields' => 'names,emailAddresses,phoneNumbers',
        ]);

        $contacts = collect($response->getConnections() ?? [])
            ->map(function ($person) {
                return [
                    'name' => optional($person->getNames()[0] ?? null)->getDisplayName() ?? 'Unnamed',
                    'email' => optional($person->getEmailAddresses()[0] ?? null)->getValue() ?? '-',
                    'phone' => optional($person->getPhoneNumbers()[0] ?? null)->getValue() ?? '-',
                ];
            })->toArray();

        session(['google_contacts' => $contacts]);

        return redirect()->route('google-contact.index')
            ->with('status', $isRefresh ? 'Contacts refreshed successfully.' : 'Contacts retrieved successfully.');
    }
}
