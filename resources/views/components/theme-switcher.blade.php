@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 p-1 rounded-xl bg-slate-200/75 dark:bg-slate-800/90 border border-slate-300/70 dark:border-slate-700 ' . $class]) }}
     role="group"
     aria-label="Pilih mode tampilan">
    <button type="button"
            @click="setTheme('light')"
            :aria-pressed="(themeMode === 'light').toString()"
            title="Mode Terang (Light)"
            :class="themeMode === 'light'
                ? 'bg-white text-amber-600 shadow-xs dark:bg-slate-700 dark:text-amber-300'
                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
            class="p-1.5 rounded-lg transition-all duration-150 focus:outline-none">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 2.78a1 1 0 011.415 0l.707.707a1 1 0 01-1.414 1.414l-.708-.707a1 1 0 010-1.414zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zm-2.78 4.22a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-4.22-2.78a1 1 0 01-1.415 0l-.707-.707a1 1 0 011.414-1.414l.708.707a1 1 0 010 1.414zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm2.78-4.22a1 1 0 010-1.414l.707-.707a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414 0zM10 5a5 5 0 100 10 5 5 0 000-10z" clip-rule="evenodd" />
        </svg>
    </button>

    <button type="button"
            @click="setTheme('dark')"
            :aria-pressed="(themeMode === 'dark').toString()"
            title="Mode Gelap (Dark)"
            :class="themeMode === 'dark'
                ? 'bg-white text-emerald-700 shadow-xs dark:bg-slate-700 dark:text-emerald-300'
                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
            class="p-1.5 rounded-lg transition-all duration-150 focus:outline-none">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
        </svg>
    </button>

    <button type="button"
            @click="setTheme('system')"
            :aria-pressed="(themeMode === 'system').toString()"
            title="Ikuti Sistem (Auto)"
            :class="themeMode === 'system'
                ? 'bg-white text-teal-700 shadow-xs dark:bg-slate-700 dark:text-teal-300'
                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
            class="p-1.5 rounded-lg transition-all duration-150 focus:outline-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
    </button>
</div>
