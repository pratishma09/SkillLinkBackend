<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BuildJobSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:projects';
    protected $description = 'Build or rebuild the project search index';

    public function handle()
    {
        DB::table('project_search_index')->truncate();

        $projects = Project::all();

        foreach ($projects as $project) {
            $text = strtolower($project->title . ' ' . $project->description);
            $terms = array_filter(preg_split('/\W+/', $text));
            $docLength = count($terms);

            $termFreq = array_count_values($terms);

            foreach ($termFreq as $term => $freq) {
                DB::table('project_search_index')->insert([
                    'project_id' => $project->id,
                    'term' => $term,
                    'term_freq' => $freq,
                    'doc_length' => $docLength,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $this->info('Project search index rebuilt successfully.');
    }
}
