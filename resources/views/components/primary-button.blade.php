<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 dark:hover:bg-indigo-400 hover:shadow-lg hover:-translate-y-0.5 focus:bg-indigo-500 dark:focus:bg-indigo-400 active:bg-indigo-700 dark:active:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-all duration-300']) }}>
    {{ $slot }}
</button>
