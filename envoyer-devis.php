<?php
/* Formulaire de devis BPB Couverture : envoi par mail() avec photos jointes.
   Fonctionne chez o2switch et OVH (PHP). Destinataire : $to. */
$to   = 'renovationartisan@gmail.com';
$from = 'devis@bp-etancheite.fr';
$json = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
function out($ok, $json, $code = 200) {
  if ($json) { header('Content-Type: application/json; charset=utf-8'); http_response_code($code); echo $ok ? '{"ok":true}' : '{"ok":false}'; exit; }
  header('Location: ' . ($ok ? '/merci/' : '/contact/?erreur=1'), true, 303); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, $json, 405);
if (!empty($_POST['_honey'])) out(true, $json);
function c($k, $max = 2000) { $v = trim(strip_tags($_POST[$k] ?? '')); $v = str_replace(["\r", "\0"], '', $v); return mb_substr($v, 0, $max); }
$nom = c('nom', 120); $tel = c('telephone', 40); $mail = c('email', 160); $commune = c('commune', 120); $service = c('service', 160); $msg = c('message', 4000);
$nom = str_replace("\n", ' ', $nom); $tel = str_replace("\n", ' ', $tel);
if ($nom === '' || $tel === '') out(false, $json, 422);
if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) $mail = '';

$body  = "Nouvelle demande de devis - BPB Couverture\n\n";
$body .= "Nom : $nom\nTéléphone : $tel\nEmail : " . ($mail ?: 'non renseigné') . "\n";
$body .= "Commune du chantier : " . ($commune ?: 'non renseignée') . "\nPrestation : " . ($service ?: 'non précisée') . "\n\n";
$body .= "Description :\n" . ($msg ?: '(aucune)') . "\n\nPage d'origine : " . ($_SERVER['HTTP_REFERER'] ?? '') . "\n";

/* Pièces jointes (5 photos, 10 Mo max au total) */
$atts = []; $total = 0;
if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
  $fi = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
  $okTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic', 'image/heif' => 'heif'];
  $n = min(count($_FILES['photos']['name']), 5);
  for ($i = 0; $i < $n; $i++) {
    if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;
    $tmp = $_FILES['photos']['tmp_name'][$i]; $size = filesize($tmp); $total += $size;
    if ($total > 10 * 1024 * 1024) break;
    $type = $fi ? finfo_file($fi, $tmp) : ($_FILES['photos']['type'][$i] ?? '');
    if (!isset($okTypes[$type])) continue;
    $atts[] = ['name' => 'photo-' . ($i + 1) . '.' . $okTypes[$type], 'type' => $type, 'data' => file_get_contents($tmp)];
  }
}

$subject = '=?UTF-8?B?' . base64_encode('Demande de devis' . ($commune ? " - $commune" : '') . ' (bp-etancheite.fr)') . '?=';
$headers = "From: Site BPB Couverture <$from>\r\n" . ($mail ? "Reply-To: $mail\r\n" : '') . "MIME-Version: 1.0\r\n";
if ($atts) {
  $bd = 'bpb' . md5(uniqid('', true));
  $headers .= "Content-Type: multipart/mixed; boundary=\"$bd\"\r\n";
  $m = "--$bd\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n$body\r\n";
  foreach ($atts as $a) {
    $m .= "--$bd\r\nContent-Type: {$a['type']}; name=\"{$a['name']}\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"{$a['name']}\"\r\n\r\n" . chunk_split(base64_encode($a['data'])) . "\r\n";
  }
  $m .= "--$bd--";
} else {
  $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
  $m = $body;
}
$ok = @mail($to, $subject, $m, $headers, '-f' . $from);
if (!$ok) $ok = @mail($to, $subject, $m, $headers);
out($ok, $json, $ok ? 200 : 500);
