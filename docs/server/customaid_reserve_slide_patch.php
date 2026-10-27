<?php
// [カスタムエイド 2026-10-26] 予約ウェブサイト(order.card-san.jp/reserve)の画面の移り方を、右から横にスライドして入るようにする
//   対象:メニュー選択 → 予約日時 → 予約内容の確認 → 予約完了 などの、次の画面へ進むとき(Card-San App の予約画面と同じ移り方)
//   「戻る」ときはスライドしない(iPhone の Safari が自分で戻る動きをするので、重ならないように)
//   やり方:reserve/index.html の </head> の前に、小さな見た目(CSS)と、画面が切り替わったのを見つける処理(JS)を足すだけ。予約の処理には触らない
// 使い方:
//   確認だけ:php -d disable_functions= /root/customaid_reserve_slide_patch.php
//   反映    :php -d disable_functions= /root/customaid_reserve_slide_patch.php apply
//   元に戻す:php -d disable_functions= /root/customaid_reserve_slide_patch.php undo
$mode = $argv[1] ?? '';
$f = '/www/wwwroot/order.card-san.jp/reserve/index.html';
$mark = 'customaid-slide-20261026';
echo $mode === 'apply' ? "=== 反映します ===\n" : ($mode === 'undo' ? "=== 元に戻します ===\n" : "=== 確認だけ(何も変更しません) ===\n");

$t = @file_get_contents($f);
if ($t === false) exit("✕ $f がありません\n");
echo "○ $f(" . strlen($t) . " バイト)\n";
$has = strpos($t, $mark) !== false;

$block = <<<HTML
<!-- [カスタムエイド 2026-10-26] $mark:次の画面へ進むとき、右から横にスライドして入る(戻るときは動かさない) -->
<style id="$mark">
html,body{overflow-x:hidden}
@keyframes csSlideIn{from{transform:translateX(100%)}to{transform:translateX(0)}}
uni-page.cs-slide-in{animation:csSlideIn .3s cubic-bezier(.25,.8,.3,1)}
</style>
<script>
(function(){
  var back=false,last=null,first=true;
  window.addEventListener('popstate',function(){back=true;});
  function check(){
    var ps=document.querySelectorAll('uni-page'),cur=null;
    for(var i=0;i<ps.length;i++){if(ps[i].offsetWidth>0||ps[i].offsetHeight>0){cur=ps[i];}}
    if(!cur||cur===last)return;
    var forward=!back&&!first;
    last=cur;first=false;back=false;
    if(!forward)return;
    cur.classList.remove('cs-slide-in');void cur.offsetWidth;cur.classList.add('cs-slide-in');
    cur.addEventListener('animationend',function h(){cur.classList.remove('cs-slide-in');cur.removeEventListener('animationend',h);});
  }
  function start(){new MutationObserver(check).observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['style','class']});check();}
  if(document.body)start();else document.addEventListener('DOMContentLoaded',start);
})();
</script>
HTML;

if ($mode === 'undo') {
    if (!$has) exit("○ まだ入っていません(何もしません)\n");
    $n = preg_replace('#<!-- \[カスタムエイド 2026-10-26\] ' . $mark . '.*?</script>\n?#su', '', $t, 1);
    $bk = '/www/backup_customaid_reserve_slide_' . date('Ymd_His') . '/';
    mkdir($bk, 0700, true); copy($f, $bk . 'index.html');
    file_put_contents($f, $n);
    exit("○ 取り除きました。控え:$bk\n");
}
if ($has) exit("○ すでに入っています(何もしません)\n");
if (substr_count($t, '</head>') !== 1) exit("✕ </head> が1つではありません。何も変更していません\n");
echo "○ </head> の前に、スライドの見た目と処理(" . strlen($block) . " バイト)を足します\n";
if ($mode !== 'apply') exit("\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。\n");

$bk = '/www/backup_customaid_reserve_slide_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
copy($f, $bk . 'index.html');
$st = stat($f);
file_put_contents($f, str_replace('</head>', $block . "\n</head>", $t));
@chown($f, $st['uid']); @chgrp($f, $st['gid']); @chmod($f, $st['mode'] & 0777);
echo "○ 反映しました。控え:$bk\n";
echo "確かめ方:iPhone の Safari で https://order.card-san.jp/reserve/?id=11 を開き(開いていたら再読み込み)、メニュー → 日時 → 確認 と進む\n";
echo "元に戻すとき:php -d disable_functions= /root/customaid_reserve_slide_patch.php undo\n";
