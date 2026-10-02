<?php

namespace App\Models\Billing;

/**
 * Plan catalogue backed by config/billing.php rather than a database table.
 *
 * The cloned billing view reads plan data through the same property names the
 * reference uses (id, name, tagline, monthly_price, yearly_price,
 * feature_labels), so this object stands in for the reference's Package model.
 */
class Plan
{
    public function __construct(
        public int $id,
        public string $key,
        public string $name,
        public string $label,
        public string $tagline,
        public float $monthly_price,
        public float $yearly_price,
        public bool $is_active,
        public array $feature_labels = [],
    ) {
    }

    public static function find(int|string|null $id): ?self
    {
        if ($id === null || $id === '') {
            return null;
        }

        return self::all()[(int) $id] ?? null;
    }

    /** @return array<int, self> keyed by plan id, in display order. */
    public static function all(): array
    {
        $moduleLabels = collect(config('modules'))->map(fn ($module) => $module['label'] ?? '');

        $plans = [];

        foreach (config('billing.by_id') as $id => $plan) {
            $featureLabels = [];

            foreach ($plan['features'] as $module => $label) {
                $featureLabels[] = $label ?: $moduleLabels->get($module, ucfirst($module));
            }

            $plans[$id] = new self(
                id: (int) $id,
                key: $plan['key'],
                name: $plan['name'],
                label: $plan['label'],
                tagline: $plan['tagline'],
                monthly_price: (float) $plan['monthly_price'],
                yearly_price: (float) $plan['yearly_price'],
                is_active: (bool) $plan['is_active'],
                feature_labels: $featureLabels,
            );
        }

        return $plans;
    }

    /** Selectable plans only — the reference hides the free tier (id 1). */
    public static function selectable(): array
    {
        return array_filter(self::all(), fn (self $plan) => $plan->id > 1 && $plan->is_active);
    }

    public function isPaid(): bool
    {
        return $this->monthly_price > 0;
    }

    public function toJsonArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tagline' => $this->tagline,
            'monthly_price' => $this->monthly_price,
            'yearly_price' => $this->yearly_price,
            'feature_labels' => $this->feature_labels,
        ];
    }
}
