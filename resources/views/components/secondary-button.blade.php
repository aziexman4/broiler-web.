<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl font-semibold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-widest shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 hover:shadow-md hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 disabled:opacity-25 transition-all duration-300']) }}>
    {{ $slot }}
</button>
