import { createDoorPreview } from './sliding-door-preview.js';
import { frameVisual, infillVisual } from './sliding-door-visuals.js';

export default function slidingDoorBuilder({ initialStep, payload, previewLabels }) {
    let preview;
    let stopWatching;
    let destroyed = false;

    return {
        activeStep: initialStep,
        catalog: payload.catalog,
        metadata: payload.metadata,
        previewConfiguration: null,
        hasPreview: false,

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
                });
                stopWatching = this.$watch('previewConfiguration', (configuration) => preview.render(configuration));
                preview.render(this.previewConfiguration);
            });
        },

        destroy() {
            destroyed = true;
            stopWatching?.();
            preview?.destroy();
        },

        frameVisual(color) {
            return frameVisual(this.metadata, color);
        },

        infillVisual(infillId) {
            return infillVisual(this.metadata, infillId);
        },
    };
}
