<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QLMService
{
    private $mu = 1500; // Dirichlet smoothing parameter

    public function search(array $queryTerms, int $corpusLength)
    {
        $results = [];

        Log::info("Starting QLM search", [
            'query_terms' => $queryTerms,
            'corpus_length' => $corpusLength,
            'mu' => $this->mu,
        ]);

        foreach ($queryTerms as $term) {
            $termCorpusFreq = DB::table('project_search_index')
                ->where('term', $term)
                ->sum('term_freq');

            // P(t|C): term probability in corpus
            $ptc = $termCorpusFreq > 0 ? $termCorpusFreq / $corpusLength : 1 / $corpusLength;

            Log::info("Processing term", [
                'term' => $term,
                'termCorpusFreq' => $termCorpusFreq,
                'ptc (P(t|C))' => $ptc,
            ]);

            $indexedTerms = DB::table('project_search_index')
                ->where('term', $term)
                ->get();

            foreach ($indexedTerms as $docTerm) {
                $projectId = $docTerm->project_id;
                $tf = $docTerm->term_freq;
                $docLength = $docTerm->doc_length;

                $ptd = ($tf + $this->mu * $ptc) / ($docLength + $this->mu);
                $score = log($ptd);

                Log::info("Document term match", [
                    'project_id' => $projectId,
                    'term' => $term,
                    'tf' => $tf,
                    'doc_length' => $docLength,
                    'ptd (P(t|D))' => $ptd,
                    'log_score' => $score,
                ]);

                if (!isset($results[$projectId])) {
                    $results[$projectId] = 0;
                }

                $results[$projectId] += $score;
            }
        }

        arsort($results);

        Log::info("Final ranked results", $results);

        return $results;
    }
}
