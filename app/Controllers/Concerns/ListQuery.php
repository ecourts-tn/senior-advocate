<?php

namespace App\Controllers\Concerns;

/**
 * Shared search / page-size / page helpers for list tables.
 */
trait ListQuery
{
    /** @var list<int> */
    protected array $allowedPerPage = [10, 25, 50, 100];

    protected int $defaultPerPage = 25;

    /**
     * @return array{q: string, perPage: int, page: int, allowedPerPage: list<int>}
     */
    protected function listQueryParams(): array
    {
        $q = trim((string) $this->request->getGet('q'));

        $perPage = (int) ($this->request->getGet('per_page') ?? $this->defaultPerPage);
        if (! in_array($perPage, $this->allowedPerPage, true)) {
            $perPage = $this->defaultPerPage;
        }

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        return [
            'q'              => $q,
            'perPage'        => $perPage,
            'page'           => $page,
            'allowedPerPage' => $this->allowedPerPage,
        ];
    }

    /**
     * Query-string extras so page-size / search survive pagination links.
     *
     * @param array<string, scalar|null> $extra
     *
     * @return array<string, scalar>
     */
    protected function listPagerQuery(array $extra = []): array
    {
        $params = $this->listQueryParams();
        $query  = array_filter(
            array_merge(
                [
                    'q'        => $params['q'] !== '' ? $params['q'] : null,
                    'per_page' => $params['perPage'],
                ],
                $extra
            ),
            static fn ($v) => $v !== null && $v !== ''
        );

        return $query;
    }
}
