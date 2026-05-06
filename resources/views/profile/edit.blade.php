<x-app-layout>
    @section('page_title', __('custom.edit_profile'))
    <div class="bg-white shadow-sm rounded-lg overflow-auto">
        <div class="p-6 bg-white shadow sm:rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:divide-x md:divide-gray-200">
                <div class="pr-0 md:pr-8">
                    @include('profile.partials.update-profile-information-form')
                </div>
                <div class="pt-8 md:pt-0 md:pl-8">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
