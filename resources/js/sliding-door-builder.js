import { createDoorPreview } from './sliding-door-preview.js';
import { frameVisual, infillVisual } from './sliding-door-visuals.js';
import { reconcilePreviewConfiguration, previewSelectionTargets, reconcilePreviewSelection } from './sliding-door-selection.js';

export default function slidingDoorBuilder({ initialStep, payload, previewLabels = {} }) {
    let preview;
    let stopWatching;
    let destroyed = false;
    let previousConfiguration = null;
    let stopSelectionWatching;
    let rootElement;

    return {
        activeStep: initialStep,
        catalog: payload.catalog,
        metadata: payload.metadata,
        // Temporary initial design until size and layout inputs are connected.
        previewConfiguration: {
            openingWidth: 3000,
            openingHeight: 2400,
            frameColor: 'black',
            panelWeights: [1, 1, 1],
        },
        hasPreview: false,
        previewDoors: [],
        previewSelection: { doorId: null, panelId: null },

        get selectedPreviewDoor() {
            return this.previewDoors.find((door) => door.id === this.previewSelection.doorId) ?? null;
        },

        get previewPanelGroupLabel() {
            const door = this.selectedPreviewDoor;
            return door
                ? (previewLabels.panels_for_door ?? '').replace(':number', String(door.index + 1))
                : (previewLabels.panels_label ?? '');
        },

        get previewSelectionAnnouncement() {
            const door = this.selectedPreviewDoor;
            if (!door) {
                return previewLabels.no_selection ?? '';
            }
            const panel = door.panels.find((candidate) => candidate.id === this.previewSelection.panelId);
            return panel
                ? (previewLabels.panel_selected ?? '').replace(':door', String(door.index + 1)).replace(':panel', String(panel.index + 1))
                : (previewLabels.door_selected ?? '').replace(':number', String(door.index + 1));
        },

        init() {
            this.$nextTick(() => {
                rootElement = this.$el;
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
            const focused = rootElement?.ownerDocument.activeElement;
            const focusedPanelId = focused?.dataset.previewPanelControl;
            const focusedDoorId = focused?.dataset.previewDoorControl;
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
                if (focusedPanelId || focusedDoorId) {
                    this.$nextTick(() => this.focusPreviewDoor(null));
                }
                return;
            }
            if (configuration !== this.previewConfiguration) {
                this.previewConfiguration = configuration;
                return;
            }
            previousConfiguration = configuration;
            this.previewDoors = previewSelectionTargets(configuration);
            this.previewSelection = reconcilePreviewSelection(this.previewSelection, this.previewDoors);
            if ((focusedPanelId && !this.previewDoors.some((door) => door.panels.some((panel) => panel.id === focusedPanelId)))
                || (focusedDoorId && !this.previewDoors.some((door) => door.id === focusedDoorId))) {
                this.$nextTick(() => this.focusPreviewDoor(this.previewSelection.doorId));
            }
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
            if (rootElement?.ownerDocument.activeElement?.hasAttribute('data-preview-panel-control')) {
                this.focusPreviewDoor(this.previewSelection.doorId);
            }
            this.previewSelection = { doorId: null, panelId: null };
        },

        focusPreviewDoor(doorId) {
            if (destroyed) {
                return;
            }
            const controls = Array.from(rootElement?.querySelectorAll('[data-preview-door-control]') ?? [])
                .filter((button) => button.getClientRects().length > 0);
            const control = controls.find((button) => button.dataset.previewDoorControl === doorId)
                ?? controls[0]
                ?? rootElement?.querySelector('.builder-step-navigation button');
            control?.focus({ preventScroll: true });
        },

        previewDoorLabel(index) {
            return previewLabels.door_label.replace(':number', String(index + 1));
        },

        previewPanelLabel(index) {
            return previewLabels.panel_short_label.replace(':number', String(index + 1));
        },

        previewPanelAccessibleLabel(index) {
            if (!this.selectedPreviewDoor) {
                return '';
            }
            return (previewLabels.panel_label ?? '')
                .replace(':door', String(this.selectedPreviewDoor.index + 1))
                .replace(':panel', String(index + 1));
        },

        frameVisual(color) {
            return frameVisual(this.metadata, color);
        },

        infillVisual(infillId) {
            return infillVisual(this.metadata, infillId);
        },
    };
}
