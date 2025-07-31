@extends('layouts.app')

@push('title', $service->title)

@section('content')
    <section class="py-24 min-h-screen">
        <div class="px-4 sm:px-6 lg:px-12 xl:px-20">
            <h2 class="text-3xl font-bold text-center mb-4 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-pink-500">{{ $service->title }}</h2>

            <p class="text-center text-base text-gray-300 max-w-xl mx-auto mb-10">
                {{ $service->description }}
            </p>

            <div class="max-w-2xl mx-auto w-full">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <a href="{{ route('google-contact.refresh') }}">
                        <button type="button" class="bg-sky-500 hover:bg-sky-600 duration-300 text-white rounded py-1 px-2 text-sm">Refresh Kontak</button>
                    </a>
                    <a href="{{ route('google-contact.backup') }}">
                        <button type="button" class="bg-green-500 hover:bg-green-600 duration-300 text-white rounded py-1 px-2 text-sm">Backup Data</button>
                    </a>
                    <a href="{{ route('google-contact.logout') }}">
                        <button type="button" class="bg-red-500 hover:bg-red-600 duration-300 text-white rounded py-1 px-2 text-sm">Logout</button>
                    </a>
                </div>

                <form method="POST" action="{{ route('google-contact.bulk-update') }}">
                    @csrf

                    <div class="flex items-center gap-2 mb-2 font-semibold text-sm text-gray-400 px-1">
                        <span class="text-center">#</span>
                        <span class="w-1/2">Name</span>
                        <span class="w-1/2">Phone Number</span>
                    </div>

                    @foreach ($contacts as $index => $contact)
                        <input type="hidden" name="contacts[{{ $index }}][resourceName]" value="{{ $contact['resourceName'] }}">
                        <input type="hidden" name="contacts[{{ $index }}][etag]" value="{{ $contact['etag'] }}">

                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm text-center text-gray-400">{{ $index + 1 }}.</span>
                            <input type="text" name="contacts[{{ $index }}][name]" value="{{ $contact['name'] }}" class="text-sm py-1 px-2 h-8 w-1/2 rounded border border-gray-300 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="off">
                            <input type="text" name="contacts[{{ $index }}][phone]" value="{{ $contact['phone'] }}" class="text-sm py-1 px-2 h-8 w-1/2 rounded border border-gray-300 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="off">
                        </div>
                    @endforeach

                    <button type="submit" class="mt-4 cursor-pointer px-5 py-2 bg-gradient-to-r from-blue-500 to-pink-500 text-white font-semibold rounded-lg shadow-lg transition duration-300 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                        Update All Contacts
                    </button>
                </form>
            </div>

        </div>
    </section>
@endsection

@push('styles')
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = Array.from(document.querySelectorAll('input[type="text"]'));
            const rowSize = 2;

            inputs.forEach((input, index) => {
                input.addEventListener('keydown', function(e) {
                    let nextIndex;

                    if (e.key === 'ArrowRight') {
                        nextIndex = index + 1;
                    } else if (e.key === 'ArrowLeft') {
                        nextIndex = index - 1;
                    } else if (e.key === 'ArrowDown') {
                        nextIndex = index + rowSize;
                    } else if (e.key === 'ArrowUp') {
                        nextIndex = index - rowSize;
                    }

                    if (nextIndex >= 0 && nextIndex < inputs.length) {
                        inputs[nextIndex].focus();
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush
