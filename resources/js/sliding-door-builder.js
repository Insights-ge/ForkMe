import { createDoorPreview } from './sliding-door-preview.js';
import { frameVisual, infillVisual } from './sliding-door-visuals.js';
import { reconcilePreviewConfiguration, previewSelectionTargets, reconcilePreviewSelection } from './sliding-door-selection.js';

export default function slidingDoorBuilder({ initialStep, payload, previewLabels }) {
    let preview;
    let stopWatching;
    let destroyed = false;
    let previousConfiguration = null;
    let stopSelectionWatching;

    return {
        activeStep: initialStep,
        catalog: payload.catalog,
        metadata: payload.metadata,
        previewConfiguration: null,
        hasPreview: false,
        previewDoors: [],
        previewSelection: { doorId: null, panelId: null },

        get selectedPreviewDoor() {
            return this.previewDoors.find((door) => door.id === this.previewSelection.doorId) ?? null;
        },

        init() {
            this.$nextTick(() => {
                const svg = this.$el.querySelector('.builder-preview-svg');
                if (destroyed || !svg) {
                    return;
                }

                preview = createDoorPreview(svg, {
                    metadata: this.metadata,
                    labels: previewLabels,
                    locale: document.documentElement.lang,
                    onRender: (available) => { this.hasPreview = available; },
                    onSelect: ({ doorId, panelId }) => {
                        if (panelId !== null) {
                            this.selectPreviewPanel(doorId, panelId);
                        } else if (doorId !== null) {
                            this.selectPreviewDoor(doorId);
                        } else {
                            this.clearPreviewSelection();
                        }
                    },
                });
                stopWatching = this.$watch('previewConfiguration', () => this.updatePreview());
                stopSelectionWatching = this.$watch('previewSelection', (selection) => preview.setSelection(selection));
                this.updatePreview();
            });
        },

        destroy() {
            destroyed = true;
            stopWatching?.();
            stopSelectionWatching?.();
            preview?.destroy();
        },

        updatePreview() {
            let configuration;
            try {
                configuration = reconcilePreviewConfiguration(this.previewConfiguration, previousConfiguration);
            } catch (error) {
                if (!(error instanceof RangeError)) {
                    throw error;
                }
                this.previewDoors = [];
                this.clearPreviewSelection();
                preview?.render(null);
                return;
            }
            if (configuration !== this.previewConfiguration) {
                this.previewConfiguration = configuration;
                return;
            }
            previousConfiguration = configuration;
            this.previewDoors = previewSelectionTargets(configuration);
            this.previewSelection = reconcilePreviewSelection(this.previewSelection, this.previewDoors);
            preview?.setSelection(this.previewSelection);
            preview?.render(configuration);
        },

        selectPreviewDoor(doorId) {
            if (this.previewDoors.some((door) => door.id === doorId)) {
                this.previewSelection = { doorId, panelId: null };
            }
        },

        selectPreviewPanel(doorId, panelId) {
            const door = this.previewDoors.find((candidate) => candidate.id === doorId);
            if (door?.panels.some((panel) => panel.id === panelId)) {
                this.previewSelection = { doorId, panelId };
            }
        },

        clearPreviewSelection() {
            this.previewSelection = { doorId: null, panelId: null };
        },

        previewDoorLabel(index) {
            return previewLabels.door_label.replace(':number', String(index + 1));
        },

        previewPanelLabel(index) {
            return previewLabels.panel_short_label.replace(':number', String(index + 1));
        },

        frameVisual(color) {
            return frameVisual(this.metadata, color);
        },

        infillVisual(infillId) {
            return infillVisual(this.metadata, infillId);
        },
    };
}
