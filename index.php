<?php

use PHPHtmlParser\Dom;

set_time_limit(9999);

require './vendor/autoload.php';

class b2bstack
{
    public $business;
    public $dom;

    public function __construct($business)
    {
        $this->business = preg_replace("/[^a-zA-Z0-9-]+/", "", $business);
        $this->dom = new Dom;
    }

    public function reviews()
    {
        $pages = $this->pages();
        $dom = $this->dom;
        
        foreach ($pages as $p => $page) {
            $dom->loadStr($page);
            foreach ($dom->find('.review-container') as $review) {

                $dom->loadStr($dom->find('.ratings'));

                foreach ($dom->find('.col-sm-12') as $x => $rating) {
                    switch ($x) {
                        case 0:
                            $recommendation = substr_count($rating, "star_100");
                            break;
                        case 1:
                            $costbenefit = substr_count($rating, "star_100");
                            break;
                        case 2:
                            $usefacility = substr_count($rating, "star_100");
                            break;
                        case 3:
                            $functionalities = substr_count($rating, "star_100");
                            break;
                        case 4:
                            $support = substr_count($rating, "star_100");
                            break;
                    }
                }

                $dom->loadStr($review);
                $reviews[explode('like-number-', $dom->find('.review-share span')[0]->getAttribute('class'))[1]] = [
                    "reviewer" => [
                        "name" => $dom->find('.name')->text,
                        "relationship" => explode(': ', $dom->find('.positions span')[1]->text)[1],
                        "avatar" => $dom->find('.reviewr img')->getAttribute('data-src')
                    ],
                    "title" => @$dom->find('.review-content h3')[0]->text,
                    "content" => [
                        "mostlike" => $dom->find('.review-content p')[6]->text,
                        "leastlike" => $dom->find('.review-content p')[8]->text,
                        "problemsolved" => $dom->find('.review-content p')[10]->text,
                    ],
                    "date" => implode("-", array_reverse(explode("/", explode('Avaliação enviada em ', $dom->find('.review-date')[0]->text)[1]))),
                    "useful" => (int) $dom->find('.review-share span')[0]->text,
                    "link" => 'https://www.b2bstack.com.br/' . explode('https://www.b2bstack.com.br/', $dom->find('.review-content a')[0]->getAttribute('href'))[1],
                    "starts" => [
                        "recommendation" => $recommendation,
                        "costbenefit" => $costbenefit,
                        "usefacility" => $usefacility,
                        "functionalities" => $functionalities,
                        "support" => $support
                    ],
                    "page" => $p + 1
                ];
            }
        }

        if (empty($reviews))
            return null;

        return $reviews;
    }

    private function pages()
    {
        $pages[] = @file_get_contents('https://www.b2bstack.com.br/product/' . $this->business . '/avaliacoes');

        $dom = $this->dom;
        $dom->loadStr($pages[0]);

        $pages_count = count($dom->find('.pagination a'));
        $pages_count = ($pages_count == 11 ? $dom->find('.pagination a')[9]->text : $pages_count);

        for ($i = 1; $i < $pages_count; $i++)
            $pages[] = @file_get_contents('https://www.b2bstack.com.br/product/' . $this->business . '/avaliacoes?order=upvoted&page=' . ($i + 1));

        return $pages;
    }
}

$reviews = new b2bstack($_GET["b"]);
echo json_encode($reviews->reviews());
