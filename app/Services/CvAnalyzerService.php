<?php

namespace App\Services;

class CvAnalyzerService
{
    public function analyze(
        string $cvText,
        string $jobSkills,
        string $jobLevel,
        int $jobExperience
    ): float
    {
        $score = 0;

        /*
        COMPETENCES = 60%
        */

        $skills = array_map(
            'trim',
            explode(',', strtolower($jobSkills))
        );

        $foundSkills = 0;

        foreach ($skills as $skill) {

            if (
                str_contains(
                    strtolower($cvText),
                    strtolower($skill)
                )
            ) {
                $foundSkills++;
            }
        }

        if (count($skills) > 0) {

            $score +=
                ($foundSkills / count($skills))
                * 60;
        }

        /*
        NIVEAU D'ETUDE = 20%
        */

        if (
            str_contains(
                strtolower($cvText),
                strtolower($jobLevel)
            )
        ) {

            $score += 20;
        }

        /*
        EXPERIENCE = 20%
        */

        preg_match_all(
            '/(\d+)\s*ans?/i',
            $cvText,
            $matches
        );

        $experienceCv = 0;

        if (!empty($matches[1])) {

            $experienceCv = max(
                array_map(
                    'intval',
                    $matches[1]
                )
            );
        }

        if (
            $experienceCv >=
            $jobExperience
        ) {

            $score += 20;

        } else {

            $score +=
                ($experienceCv / max(1, $jobExperience))
                * 20;
        }

        return round($score, 2);
    }
}
