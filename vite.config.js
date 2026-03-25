import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: 'adminmenu/js/dist',
        lib: {
            entry: 'adminmenu/js/pagebuilder.js',
            name: 'BbfPagebuilder',
            fileName: 'bbf-pagebuilder',
            formats: ['iife'],
        },
        rollupOptions: {
            output: {
                assetFileNames: 'bbf-pagebuilder.[ext]',
            },
        },
    },
});
