?php

function scrape_imdb($year_start, $year_end, $page_start, $page_end){

$curl = curl_init();
$all_data = array();

for ($page = $page_start; $page <= $page_end; $page++){

$url = "https://www.imdb.com/search/title/?year=2000,2000&title_type=feature&sort=moviemeter,asc&page=1&ref_=adv_nxt%22&";

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($curl);

$movies = array();

//match movie title
preg_match_all('!<a href="\/title\/.*?\/\?ref_=adv_li_tt"\n>(.*?)<\/a>!',$result,$match);
$movies['title'] = $match[1];

//match year
preg_match_all('!<span class="lister-item-year text-muted unbold">.*?\((\d{4})\)<\/span>!',$result,$match);
$movies['year'] = $match[1];

//match image url
preg_match_all('!loadlate="(.*?)"!',$result,$match);
$movies['image'] = $match[1];

//match certificate, runtime, genre block
preg_match_all("!<p class=\"text-muted\s\">(.*?)<\/p>!is",$result, $match);

//match certificate, runtime, genre individually from above block
for ($i=0;$i<count($match[1]);$i++){

    //match certificate
    if (preg_match('!<span class="certificate">(.*?)<\/span>!',$match[1][$i],$certificate)){
        $movies['certificate'][$i] = $certificate[1];
    }
    else {
        $movies['certificate'][$i] = '';
    }

    //match runtime
    if (preg_match('!<span class="runtime">(\d{2,3}) min<\/span>!',$match[1][$i],$runtime)){
        $movies['runtime'][$i] = $runtime[1];
    }
    else {
        $movies['runtime'][$i] = '';
    }

    //match genre
    if (preg_match('!<span class="genre">\n(.*?)\s*?<\/span>!',$match[1][$i],$genre)){
        $movies['genres'][$i] = $genre[1];
    }
    else {
        $movies['genres'][$i] = '';
    }
}

//match ratings bar block
preg_match_all('!<div class="ratings-bar">(.*?)<\/span>!is',$result,$match);

//match ratings individually
for ($i=0;$i<count($match[1]);$i++)
{
    if (preg_match("!data-value=\"(.*?)\"!i",$match[1][$i],$imdb_rating)){
        $movies['imdb_rating'][$i] = $imdb_rating[1];
    }
    else {
        $movies['imdb_rating'][$i] = '';
    }
}

//match the metascore and description together, make metascore optional
preg_match_all('!(<div class="inline-block ratings-metascore">
<span class="metascore  (favorable|mixed|unfavorable)">(.*?)\s*?<\/span>\s*?Metascore\s*?<\/div>\s*?<\/div>\n)?<p class="text-muted">(.*?)<\/p>!is',$result,$match);

for ($i=0;$i<count($match[0]);$i++){
    if (preg_match('!metascore  (favorable|mixed|unfavorable)">(.*?)\s*?</span>!',$match[0][$i],$metascore)){
        $movies['metascore'][$i] = $metascore[2];
    }
    else {
        $movies['metascore'][$i] = '';
    }

    if (preg_match('!<p class="text-muted\s?">\n(.*?)</p>!i',$match[0][$i],$description)){
        $movies['description'][$i] = $description[1];
    }
    else {
        $movies['description'][$i] = '';
    }

}

//match directors and stars block
preg_match_all('!<p class="">(.*?)<\/p>!is',$result,$match);
for ($i=0;$i<count($match[1]);$i++){
    if (preg_match('!Directors?:\n<a href="/name/.*?/?ref_=adv_li_dr_0"\n>(.*?)</a>\n!s',$match[1][$i],$directors))
    {
        //print_r($directors);die;
        $clean_directors = preg_replace('!(<a href="\/name\/.*?\/?ref_=adv_li_dr_\d"\n>|<\/a>|\n)!','',$directors[1]);
        $movies['directors'][$i] = $clean_directors;
    }
    else {
       $movies['directors'][$i] = '';
    }
    if (preg_match('!Stars?:\n(.*?)<\/a>\n!is',$match[1][$i],$stars)){
        preg_match_all('!>(.*?)<!',$stars[1],$all_stars);
        $movies['stars'][$i] = implode(', ',$all_stars[1]);
    }
    else {
        $movies['stars'][$i] = '';
    }
}

//match votes block, votes and gross, (because Gross can be empty)
//votes or gross can be empty, they can also both be empty, make them both optional
$regex = '!(<p class="sort-num_votes-visible">
\s*?<span class="text-muted">Votes:<\/span>
\s*?<span name="nv" data-value="(\d*?)">.*?<\/span>)?.*?
(<span class="ghost">\|<\/span>\s*?<span class="text-muted">Gross:<\/span>
\s*?<span name="nv" data-value="(.*?)">.*?<\/span>
\s*?<\/p>)?(\s*?<\/div>\s*?<\/div>\s*?)(<div class="lister-item mode-advanced">|</div>\s*?<div class="nav">)!is';

//<div class="lister-item mode-advanced">

preg_match_all($regex,$result,$match);

for ($i=0;$i<count($match[0]);$i++){
    if (preg_match('!Votes:</span>\s*?<span name="nv" data-value="(\d*?)">!is',$match[0][$i],$votes)){
        $movies['votes'][$i] = $votes[1];
    }
    else {
        $movies['votes'][$i] = '';
    }
    if (preg_match('!Gross:</span>\s*?<span name="nv" data-value="(.*?)">!is',$match[0][$i],$gross)){
        $movies['gross'][$i] = $gross[1];
    }
    else {
        $movies['gross'][$i] = '';
    }
    }

    //save all data in a nicely formatted array
    foreach($movies as $key => $value) {
        for ($i = 0; $i < count ($movies[$key]);$i++){
            $data[$i][$key] = $movies[$key][$i];
        }
    }

    $all_data = array_merge($data,$all_data);

    } //end main loop

    return $all_data;
}
?>
