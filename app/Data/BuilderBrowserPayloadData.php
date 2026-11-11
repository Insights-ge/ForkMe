<?php

namespace App\Data;

use JsonSerializable;
use stdClass;

readonly class BuilderBrowserPayloadData implements JsonSerializable
{
    public function __construct(
        public BuilderCatalogData $catalog,
    ) {}

    /**
     * Metadata is reserved for presentation presets and visual mappings.
     * It must not be merged into catalog records or imply manufacturing rules.
     *
     * @return array{catalog: BuilderCatalogData, metadata: stdClass}
     */
    public function jsonSerialize(): array
    {
        return [
            'catalog' => $this->catalog,
            'metadata' => new stdClass,
        ];
    }
}
