<?php

 $curl = curl_init();

 $url = "https://www.canada.ca/en/immigration-refugees-citizenship/services/immigrate-canada/express-entry/submit-profile/rounds-invitations.html";

 curl_setopt($curl, CURLOPT_URL, $url);
 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

 $result = curl_exec($curl);

 $sections = array();

 //match section
 preg_match_all('!<p><strong>.*?<\/strong><\/p>!', $result, $match);
 preg_match_all('!<p><strong>.*?<\/strong>.*?<\/p>!', $result, $match1);
 // $sections['name'] = $match[1];

 print_r($match[0][0]);

 print_r($match1[0][1]);
 print_r($match1[0][2]);
 print_r($match1[0][3]);
 print_r($match1[0][4]);
 print_r($match1[0][5]);
 print_r($match1[0][6]);

 preg_match_all('!<li>.*?<\/li>!', $result, $match2);

  print_r($match2[0][16]);
 die;

?>
