/**
 * BBF Events – GrapesJS Pagebuilder
 * Main entry point for Vite bundling.
 */

import grapesjs from 'grapesjs';
import grapesjsBlocksBootstrap5 from 'grapesjs-blocks-bootstrap5';
import grapesjsPresetWebpage from 'grapesjs-preset-webpage';
import { registerBbfBlocks } from './pagebuilder-blocks.js';
import { setupBbfPanels } from './pagebuilder-panels.js';
import { setupAssetManager } from './pagebuilder-assets.js';
import { setupStorage } from './pagebuilder-storage.js';

window.BbfPagebuilder = {
    init(config) {
        const {
            container = '#bbf-pagebuilder',
            eventId,
            languageIso = 'ger',
            csrfToken = '',
            canvasStyles = [],
            canvasScripts = [],
        } = config;

        const editor = grapesjs.init({
            container,
            fromElement: false,
            height: '100vh',
            width: 'auto',
            storageManager: false,

            canvas: {
                styles: [
                    ...canvasStyles,
                ],
                scripts: [
                    ...canvasScripts,
                ],
            },

            panels: { defaults: [] },

            deviceManager: {
                devices: [
                    { name: 'Desktop', width: '' },
                    { name: 'Tablet', width: '768px', widthMedia: '992px' },
                    { name: 'Mobile', width: '375px', widthMedia: '768px' },
                ],
            },

            assetManager: {
                upload: `/admin/plugin/bbfdesign_events/api/media/upload`,
                uploadName: 'files',
                headers: { 'X-CSRF-Token': csrfToken },
                autoAdd: true,
                dropzone: true,
                openAssetsOnDrop: true,
                assets: [],
            },

            styleManager: {
                sectors: [
                    {
                        name: 'Abmessungen',
                        properties: [
                            'width', 'min-width', 'max-width',
                            'height', 'min-height',
                            'padding', 'margin',
                        ],
                    },
                    {
                        name: 'Typografie',
                        properties: [
                            'font-family', 'font-size', 'font-weight',
                            'letter-spacing', 'line-height',
                            'color', 'text-align', 'text-transform',
                        ],
                    },
                    {
                        name: 'Hintergrund',
                        properties: [
                            'background-color', 'background-image',
                            'background-size', 'background-position',
                        ],
                    },
                    {
                        name: 'Rahmen & Ecken',
                        properties: [
                            'border', 'border-radius', 'box-shadow',
                        ],
                    },
                ],
            },

            plugins: [grapesjsBlocksBootstrap5, grapesjsPresetWebpage],
            pluginsOpts: {
                [grapesjsBlocksBootstrap5]: {
                    blocks: {
                        container: true,
                        row: true,
                        column: true,
                        column_break: true,
                        alert: true,
                        tabs: true,
                        badge: true,
                        card: true,
                        card_container: true,
                        collapse: true,
                    },
                    blockCategories: {
                        layout: 'Bootstrap Layout',
                        components: 'Bootstrap Komponenten',
                        typography: 'Typografie',
                        media: 'Medien',
                        forms: 'Formulare',
                    },
                },
                [grapesjsPresetWebpage]: {
                    modalImportTitle: 'HTML importieren',
                    modalImportLabel: 'HTML-Code hier einfügen',
                    modalImportButton: 'Importieren',
                },
            },
        });

        // Register custom BBF blocks
        registerBbfBlocks(editor);

        // Setup custom panels & toolbar
        setupBbfPanels(editor);

        // Setup asset manager integration
        setupAssetManager(editor, csrfToken);

        // Setup save/load
        setupStorage(editor, eventId, languageIso, csrfToken);

        return editor;
    },
};
