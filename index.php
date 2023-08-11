<HTML>
    <HEAD>
        <?php
        $API_key = 'YOUR_SECRET_YOUTUBE_API_KEY'; // You can have it, too. It's a free. https://developers.google.com/youtube/v3
        $maxResults = 6; 

        //Get videos from channel by YouTube Data API
        $numberVideo = 0;
        $apiError = 'Videa nenalezena.';
        try{
            $apiData = @file_get_contents('https://youtube.googleapis.com/youtube/v3/playlistItems?part=contentDetails&maxResults='.$maxResults.'&playlistId=PLkxVRvKE6JjDUHBYn_ZA46wrBZDH8jdgx&key='.$API_key);
            if($apiData){
                $videoList = json_decode($apiData);
            }else{
                throw new Exception('Načítání nových videí právě nefunguje. Kontaktujte admina.<br>Admine: Invalid API key or something different.');
            }
        }catch(Exception $e){
            $apiError = $e->getMessage();
        }
        ?>
        <TITLE>Kdy bude Bašta? | Neoficiální předpověď</TITLE>
        <meta name="keywords" content="Bašta, YouTube, Kdy bude Bašta?">
        <STYLE>
            body {
              margin: 0;
              font-family: Arial, Helvetica, sans-serif;
              background-image: url("background.png");
              background-color: #000000;
              background-position: center;
              background-repeat: no-repeat;
              background-size: cover;
              position: relative;
            }

            .hero-text {
              text-align: center;
              position: absolute;
              top: 50%;
              left: 50%;
              transform: translate(-50%, -50%);
              color: white;
            }
            
            .button {
                height: 30px;
            }
        </STYLE>
        <?php 
        function PocetDniMeziDatumy($starsi, $novejsi){
            $rozdil = strtotime("$novejsi") - strtotime("$starsi");
            // Jestli výsledek mezi daty je záporny, tak vrat kladný výsledek
            if($rozdil < 0){
                $rozdil = 0 - $rozdil;
            }
            return round($rozdil / (60 * 60 * 24));
        }
        $previous =  $videoList->items[0]->contentDetails->videoPublishedAt;
        $i = 1;
        $max = 0;
        $suma = 0;
        while($i < $maxResults){ 
            $predchoziDatum = $videoList->items[$i-1]->contentDetails->videoPublishedAt;
            $aktualniDatum = $videoList->items[$i]->contentDetails->videoPublishedAt; 
            $i++;
            $rozdilMeziDny = PocetDniMeziDatumy($predchoziDatum, $aktualniDatum);
            if ($max < $rozdilMeziDny){
                $max = $rozdilMeziDny;
            };
            $suma = $suma + $rozdilMeziDny;
        }
        
        $stredniHodnota = $suma/$i;
        $date = new DateTime($videoList->items[0]->contentDetails->videoPublishedAt); // Y-m-d
        $date->add(new DateInterval('P'.round($stredniHodnota).'D'));
        ?>
            </HEAD>
    <BODY>
        <!--<img src="logo.png" style="width: 500px"> -->
        <div class="hero-text">
            <div class="classLogo"><img class="logo" src="logo.png" style="width: 250px"></div>
            <h1>Podle statistické předpovědi bude <u>asi:</u></br>
            <?php
                echo "<a style='font-size:120px'>".$date->format('d. m. Y')."</a></h1>"; // Výpis datumu
            ?>
            <?php
            $max = 70;
            $min = 70;
            $suma = 0;
            
            function TakeData($nextToken){
                $API_key = 'AIzaSyA7d7oU0NrdI7QA_DotL91igVuO0msgjFo';
                $maxResults = 25; 
                $apiError = 'Videa nenalezena.';
                if($nextToken == 'Bez'){
                    try{
                        $apiData = @file_get_contents('https://youtube.googleapis.com/youtube/v3/playlistItems?part=contentDetails&maxResults='.$maxResults.'&playlistId=PLkxVRvKE6JjDUHBYn_ZA46wrBZDH8jdgx&key='.$API_key);
                        if($apiData){
                            $videoList = json_decode($apiData);
                        }else{
                            throw new Exception('Načítání nových videí právě nefunguje. Kontaktujte admina.<br>Admine: Invalid API key or something different. ID: 4');
                        }
                    }catch(Exception $e){
                       $apiError = $e->getMessage();
                    }
                    
                }else{
                    try{
                        $apiData = @file_get_contents('https://youtube.googleapis.com/youtube/v3/playlistItems?part=snippet%2CcontentDetails&maxResults='.$maxResults.'&pageToken='.$nextToken.'&playlistId=PLkxVRvKE6JjDUHBYn_ZA46wrBZDH8jdgx&key='.$API_key);
                        if($apiData){
                            $videoList = json_decode($apiData);
                        }else{
                            throw new Exception('Načítání nových videí právě nefunguje. Kontaktujte admina.<br>Admine: Invalid API key or something different. ID: 5');
                        }
                    }catch(Exception $e){
                       $apiError = $e->getMessage();
                    }
                }
                return $videoList;
            }
            
            function searching(){
                $max = 0; $min = 73; $suma = 0; $preskocit = 'true';
                $podminka = 'Bez';
                $predchoziDatum;
                
                while(!empty($podminka)){
                    $videoList = TakeData($podminka);
                    foreach($videoList->items as $item){ 
                        if($preskocit == 'false'){ // Preskoceni prvni polozky
                            $rozdilMeziDny = PocetDniMeziDatumy($predchoziDatum, $item->contentDetails->videoPublishedAt);
                            if ($max < $rozdilMeziDny){
                                $max = $rozdilMeziDny;
                            }
                            if ($min > $rozdilMeziDny){
                                $min = $rozdilMeziDny;
                            }
                            $suma = $suma + $rozdilMeziDny;
                            $predchoziDatum = $item->contentDetails->videoPublishedAt;
                        }else{
                            $preskocit = 'false';
                            if($podminka == 'Bez'){
                                $predchoziDatum = $item->contentDetails->videoPublishedAt;
                            }
                        }
                    }
                    $podminka = $videoList->nextPageToken;
                }
                return $max." ".$min." ".$suma;
            }
            $vysledek = searching();
            $rozdelene = explode(" ", $vysledek);
            
            $max    = $rozdelene[0];
            $min    = $rozdelene[1];
            $suma   = $rozdelene[2];
            ?>
             <h3>Průměrná doba trvání: 39,8 dnů<br>Nejméně byla po: <?php echo $min; ?> dnech<br>Nejdéle byla po: <?php echo $max; ?> dnech</h3>
             </br>
             <div>
                <a href="https://www.youtube.com/@AtiShow"><img class="button" 
                src="YouTube.png"></a> <a href="https://www.instagram.com/atishows/"><img class="button" 
                src="Instagram.png"></a> <a href="https://www.tiktok.com/@atishows"><img class="button" 
                src="TikTok.png"></a> <a href="https://www.realgeek.cz/14-ati"><img class="button" 
                src="Realgeek.png"></a> <a href="https://twitter.com/atishows"><img class="button" 
                src="Twitter.png"></a><br><br>
                <span style='font-size:14px; color: grey'> <a style='color: grey' href="https://www.youtube.com/@vitoxczechia/about">Kontakt na tvůrce webu</a></br>
                Tento web není provozován AtiShow</span>
             </div>
        </div>
        <a href="https://www.toplist.cz/stat/1824115/"><script language="JavaScript" type="text/javascript" charset="utf-8">
        <!--
        document.write('<img src="https://toplist.cz/dot.asp?id=1824115&http='+
encodeURIComponent(document.referrer)+'&t='+encodeURIComponent(document.title)+'&l='+encodeURIComponent(document.URL)+
'&wi='+encodeURIComponent(window.screen.width)+'&he='+encodeURIComponent(window.screen.height)+'&cd='+
encodeURIComponent(window.screen.colorDepth)+'" width="1" height="1" border=0 alt="TOPlist" />');
//--></script><noscript><img src="https://toplist.cz/dot.asp?id=1824115&njs=1" border="0"
alt="TOPlist" width="1" height="1" /></noscript></a>
    </BODY>

</HTML>
