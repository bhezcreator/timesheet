<div data-modal="{{ $id }}" class="hidden fixed inset-0 z-50">
    <!-- Overlay -->
    <div class="modal-overlay fixed inset-0 bg-gray-900/60 backdrop-blur-md transition-opacity"></div>

    <!-- Wrapper -->
    <div class="relative
    min-h-screen
    flex
    items-start
    justify-center
    p-4
    sm:p-6
    pt-16">

        <!-- Modal Card -->
        <div class="modal-content relative w-full {{ $sizeClass() }} bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 transform transition-all">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 tracking-tight">
                        {{ $title }}
                    </h3>
                    @isset($subtitle)
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $subtitle }}
                        </p>
                    @endisset
                </div>

                <!-- Close Button -->
                <button
                    type="button"
                    data-close-modal
                    class="flex items-center justify-center w-9 h-9 rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition duration-200 cursor-pointer"
                >
                    <i class="las la-times text-xl"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @isset($footer)
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $footer }}
                </div>
            @endisset

        </div>
    </div>
</div>
