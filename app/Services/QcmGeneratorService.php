<?php

namespace App\Services;

class QcmGeneratorService
{
    public function generate(string $skills)
    {
        $bank = [

            'laravel' => [
                [
                    'question' => 'Laravel est un framework ?',
                    'a' => 'Python',
                    'b' => 'PHP',
                    'c' => 'Java',
                    'd' => 'C#',
                    'answer' => 'b'
                ],
                [
                    'question' => 'Quel ORM utilise Laravel ?',
                    'a' => 'Hibernate',
                    'b' => 'Doctrine',
                    'c' => 'Eloquent',
                    'd' => 'Prisma',
                    'answer' => 'c'
                ]
            ],

            'react' => [
                [
                    'question' => 'Quel hook gère un état ?',
                    'a' => 'useEffect',
                    'b' => 'useState',
                    'c' => 'useMemo',
                    'd' => 'useRef',
                    'answer' => 'b'
                ]
            ],

            'php' => [
                [
                    'question' => 'PHP signifie ?',
                    'a' => 'Personal Home Page',
                    'b' => 'PHP Hypertext Preprocessor',
                    'c' => 'Private Hyper Program',
                    'd' => 'Program Hypertext Page',
                    'answer' => 'b'
                ]
            ]
        ];

        $questions = [];

        $skillsArray = explode(',', strtolower($skills));

        foreach ($skillsArray as $skill) {

            $skill = trim($skill);

            if (isset($bank[$skill])) {

                $randomQuestion =
                    $bank[$skill][array_rand($bank[$skill])];

                $questions[] = $randomQuestion;
            }
        }

        shuffle($questions);

        return array_slice($questions, 0, 10);
    }
}
