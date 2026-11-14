<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Log Aktivitas Sistem (Audit Trail)') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="liveSearch()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-md sm:rounded-xl">
                <div class="p-6 text-slate-900">
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-slate-900">Jejak Aktivitas</h3>
                            <p class="mt-1 text-sm text-slate-500">Melihat daftar semua aktivitas (tambah, edit, hapus) yang dilakukan oleh pengguna.</p>
                        </div>
                        <div class="relative w-full sm:w-72">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input type="text" x-model="search" @input.debounce.300ms="fetchData()" class="block w-full p-2 pl-10 text-sm text-slate-900 border border-slate-300 rounded-xl bg-slate-50 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Cari aktivitas, modul, pengguna...">
                            <!-- Loading spinner -->
                            <div x-show="isLoading" class="absolute inset-y-0 right-0 flex items-center pr-3" style="display: none;" x-cloak>
                                <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div id="table-container" class="relative transition-opacity duration-300" :class="isLoading ? 'opacity-50' : 'opacity-100'">
                        @include('activity_logs.partials.table_data')
                    </div>
                </div>
            </div>
        </div>

        <!-- AlpineJS Logic -->
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('liveSearch', () => ({
                    search: '',
                    isLoading: false,
                    
                    async fetchData(url = null) {
                        this.isLoading = true;
                        
                        // Use provided URL (for pagination) or build new one for search
                        const fetchUrl = url || `/activity-logs?search=${encodeURIComponent(this.search)}`;
                        
                        try {
                            const response = await fetch(fetchUrl, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html'
                                }
                            });
                            
                            if (response.ok) {
                                const html = await response.text();
                                document.getElementById('table-container').innerHTML = html;
                                this.attachPaginationListeners();
                            }
                        } catch (error) {
                            console.error('Search failed:', error);
                        } finally {
                            this.isLoading = false;
                        }
                    },
                    
                    attachPaginationListeners() {
                        const links = document.querySelectorAll('.ajax-pagination a');
                        links.forEach(link => {
                            link.addEventListener('click', (e) => {
                                e.preventDefault();
                                // Append current search query to the pagination URL
                                const url = new URL(link.href);
                                if (this.search) {
                                    url.searchParams.set('search', this.search);
                                }
                                this.fetchData(url.toString());
                            });
                        });
                    },
                    
                    init() {
                        this.attachPaginationListeners();
                    }
                }));
            });
        </script>
    </div>
</x-app-layout>
