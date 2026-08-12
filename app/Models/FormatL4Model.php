<?php

namespace App\Models;

use CodeIgniter\Model;

class FormatL4Model extends Model
{
    protected $table            = 'format_l4_entries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'application_id', 's_no', 'topic', 'articles', 'books',
        'teaching_assignment', 'guest_lectures', 'other_details',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function replaceForApplication(int $applicationId, array $rows): void
    {
        $this->where('application_id', $applicationId)->delete();
        $s = 1;
        foreach ($rows as $row) {
            $articles = trim((string) ($row['articles'] ?? ''));
            $books    = trim((string) ($row['books'] ?? ''));
            // Legacy combined column kept for older exports / fallbacks.
            if ($articles !== '' || $books !== '') {
                $row['topic'] = trim($articles . ($articles !== '' && $books !== '' ? "\n" : '') . $books);
            } elseif (! empty($row['topic'])) {
                // Older posts that only sent topic → treat as articles.
                $row['articles'] = (string) $row['topic'];
            }

            $check = $row;
            unset($check['application_id'], $check['s_no']);
            if (empty(array_filter($check, static fn ($v) => trim((string) $v) !== ''))) {
                continue;
            }
            $row['application_id'] = $applicationId;
            $row['s_no']           = $s++;
            $this->insert($row);
        }
    }

    public function forApplication(int $applicationId): array
    {
        return $this->where('application_id', $applicationId)->orderBy('s_no', 'ASC')->findAll();
    }
}
