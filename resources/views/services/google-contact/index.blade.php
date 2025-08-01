@extends('layouts.app')

@push('title', $service->title)

@section('content')
    <section class="py-24 min-h-screen">
        <div class="px-4 sm:px-6 lg:px-12 xl:px-20">
            <h2 class="text-3xl font-bold text-center mb-4 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-pink-500">{{ $service->title }}</h2>

            <p class="text-center text-base text-gray-300 max-w-xl mx-auto mb-10">
                {{ $service->description }}
            </p>

            <div class="mx-auto w-full space-y-4">
                @if (count($contacts))
                    <div class="flex flex-wrap items-center justify-end gap-2 mb-6">
                        <a href="{{ route('google-contact.refresh') }}">
                            <button type="button" class="cursor-pointer bg-sky-500 hover:bg-sky-600 duration-300 text-white rounded py-1 px-2 text-sm">Refresh Kontak</button>
                        </a>
                        <a href="{{ route('google-contact.backup') }}">
                            <button type="button" class="cursor-pointer bg-slate-500 hover:bg-slate-600 duration-300 text-white rounded py-1 px-2 text-sm">Backup Data</button>
                        </a>
                        <a href="{{ route('google-contact.logout') }}">
                            <button type="button" class="cursor-pointer bg-red-500 hover:bg-red-600 duration-300 text-white rounded py-1 px-2 text-sm">Logout</button>
                        </a>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <form method="POST" action="{{ route('google-contact.add') }}">
                        @csrf
                        <h3 class="text-sm text-gray-300 font-semibold mb-2">Add New Contact</h3>

                        <div class="mb-4 space-y-2">
                            <input type="text" name="name" placeholder="New Name" required class="text-sm py-1 px-2 h-8 w-full rounded border border-gray-300 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-400" autocomplete="off">
                            <input type="text" name="phone" placeholder="New Phone Number" required class="text-sm py-1 px-2 h-8 w-full rounded border border-gray-300 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-400" autocomplete="off">
                        </div>

                        <button type="submit" class="block me-auto cursor-pointer px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow-lg transition duration-300">
                            Add Contact
                        </button>
                    </form>

                    <div class="col-span-2">
                        @if (count($contacts))
                            <form method="POST" action="{{ route('google-contact.bulk-update') }}">
                                @csrf

                                <div class="flex items-center gap-2 mb-2 font-semibold text-sm text-gray-300 px-1">
                                    <span class="text-center">#</span>
                                    <span class="w-1/2">Name</span>
                                    <span class="w-1/2">Phone Number</span>
                                </div>

                                @foreach ($contacts as $index => $contact)
                                    <input type="hidden" name="contacts[{{ $index }}][resourceName]" value="{{ $contact['resourceName'] }}">
                                    <input type="hidden" name="contacts[{{ $index }}][etag]" value="{{ $contact['etag'] }}">

                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="text-sm text-center text-gray-300">{{ $index + 1 }}.</span>
                                        <input type="text" name="contacts[{{ $index }}][name]" value="{{ $contact['name'] }}" placeholder="Name" class="text-sm py-1 px-2 h-8 w-1/2 rounded border border-gray-300 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="off">
                                        <input type="text" name="contacts[{{ $index }}][phone]" value="{{ $contact['phone'] }}" placeholder="Phone Number" class="text-sm py-1 px-2 h-8 w-1/2 rounded border border-gray-300 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="off">
                                    </div>
                                @endforeach

                                <button type="submit" class="cursor-pointer px-5 py-2 bg-gradient-to-r from-blue-500 to-pink-500 text-white font-semibold rounded-lg shadow-lg transition duration-300 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Update All Contacts
                                </button>
                            </form>
                        @else
                            <div class="text-center text-gray-300 py-10">
                                <p>No contacts found. Please refresh your contacts.</p>

                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <a href="{{ route('google-contact.refresh') }}">
                                        <button type="button" class="cursor-pointer mt-4 bg-sky-500 hover:bg-sky-600 duration-300 text-white rounded py-2 px-4 text-sm">Refresh Contacts</button>
                                    </a>
                                    <a href="{{ route('google-contact.logout') }}">
                                        <button type="button" class="cursor-pointer mt-4 bg-red-500 hover:bg-red-600 duration-300 text-white rounded py-2 px-4 text-sm">Logout</button>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

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
