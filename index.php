<?php

use PHPHtmlParser\Dom;

require './vendor/autoload.php';

class b2bstack
{
    public $business;
    public $dom;

    public function __construct($business)
    {
        $this->business = $business;
        $this->dom = new Dom;
    }

    public function reviews()
    {
        $pages = $this->pages();
        $dom = $this->dom;

        foreach ($pages as $page) {
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
                    "useful" => $dom->find('.review-share span')[0]->text,
                    "link" => 'https://www.b2bstack.com.br/'.explode('https://www.b2bstack.com.br/', $dom->find('.review-content a')[0]->getAttribute('href'))[1],
                    "starts" => [
                        "recommendation" => $recommendation,
                        "costbenefit" => $costbenefit,
                        "usefacility" => $usefacility,
                        "functionalities" => $functionalities,
                        "support" => $support
                    ]
                ];
            }
        }

        return $reviews;
    }

    private function pages()
    {
        $mc = JMathai\PhpMultiCurl\MultiCurl::getInstance();
        $pages[] = $mc->addUrl('https://www.b2bstack.com.br/product/' . $this->business . '/avaliacoes')->response;

        $dom = $this->dom;
        $dom->loadStr($pages[0]);

        $pages_count = count($dom->find('.pagination a'));
        
        for ($i = 1; $i < $pages_count; $i++)
            $pages[] = $mc->addUrl('https://www.b2bstack.com.br/product/' . $this->business . '/avaliacoes?commit=++Popularidade+&order=upvoted&page=' . ($i + 1) . '&utf8=%E2%9C%93');
        
        foreach ($pages as $key => $page)
            $return[] = ($key == 0 ? $page : $page->response);

        //echo $mc->getSequence()->renderAscii(); // Output a call sequence diagram to see how the parallel calls performed.

        return $return;
    }
}

$reviews = new b2bstack("cpf-cnpj");
echo "<pre>";
print_r($reviews->reviews());
