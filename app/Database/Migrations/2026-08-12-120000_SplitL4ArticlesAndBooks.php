<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Format L-4 prescribed columns split "Topic of published academic
 * Articles/Books" into separate Articles and Books fields.
 */
class SplitL4ArticlesAndBooks extends Migration
{
    public function up()
    {
        $this->forge->addColumn('format_l4_entries', [
            'articles' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'topic',
            ],
            'books' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'articles',
            ],
        ]);

        // Preserve existing free-text topics under Articles.
        $this->db->query(
            "UPDATE format_l4_entries
             SET articles = topic
             WHERE (articles IS NULL OR articles = '')
               AND topic IS NOT NULL
               AND topic <> ''"
        );
    }

    public function down()
    {
        // Best-effort: recombine into topic before drop.
        $this->db->query(
            "UPDATE format_l4_entries
             SET topic = TRIM(BOTH E'\\n' FROM CONCAT_WS(E'\\n',
                 NULLIF(TRIM(COALESCE(articles, '')), ''),
                 NULLIF(TRIM(COALESCE(books, '')), '')
             ))
             WHERE (topic IS NULL OR topic = '')
               AND (
                   (articles IS NOT NULL AND articles <> '')
                   OR (books IS NOT NULL AND books <> '')
               )"
        );

        $this->forge->dropColumn('format_l4_entries', ['articles', 'books']);
    }
}
