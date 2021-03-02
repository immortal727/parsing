<?php
require 'vendor/autoload.php';
require_once 'function.php';
require_once('phpQuery/phpQuery/phpQuery.php');

use Web\Parser;

$html = Parser::getPage([
    "url"       => "https://www.svyaznoy.ru/catalog/phone/224",
    "timeout" => 10
]);

if(!empty($html["data"])){
    $content = $html["data"]["content"];
    phpQuery::newDocument($content);

    $categories = pq(".b-category-menu")->find(".b-category-menu__link");

    $tmp = [];

    foreach($categories as $key => $category){

        $category = pq($category);

        $tmp[$key] = [
            "text" => trim($category->text()),
            "url"  => trim($category->attr("href"))
        ];

        $submenu = $category->next(".b-category-submenu")->find(".b-category-submenu__link");

        foreach($submenu as $submen){

            $submen = pq($submen);

            $tmp[$key]["submenu"][] = [
                "text" => trim($submen->text()),
                "url"  => trim($submen->attr("href"))
            ];
        }
    }

    phpQuery::unloadDocuments();
}
?>

<?php dd($tmp); ?>

<ul>
    <?php foreach($tmp as $value): ?>
         <li>
              <a href="https://www.svyaznoy.ru <? echo($value["url"]); ?>" target="_blank">
                   <?php echo($value["text"]); ?>
              </a>
              <ul>
                  <? if(!empty($value["submenu"])): ?>
                    <?php foreach($value["submenu"] as $val): ?>
                    <li>
                      <a href="https://www.svyaznoy.ru<?php echo($val["url"]); ?>" target="_blank">
                         <?php echo($val["text"]); ?>
                        </a>
                  </li>
                 <?php endforeach; ?>
                  <? endif; ?>
              </ul>
         </li>
    <?php endforeach; ?>
</ul>