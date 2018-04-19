<?
// composer
require_once $_SERVER["DOCUMENT_ROOT"] . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Keypoint\Rest\Controller as RestController;
use Keypoint\Tools\Rest\Controller as MethodController;
use Keypoint\Tools\Rest\Routes;
use Bitrix\Main\Loader;

$app = new Silex\Application();
$app['debug'] = true;
$baseURL = '/rest';

if(!Loader::includeModule('keypoint.rest')) {
    $errorData = [
        "errorType" => "internal",
        "errorMessage" => "Не подключен модуль keypoint.rest",
    ];
    return $app->json($errorData, 500);
}

$rest = new RestController($app, MethodController::class);

/**
 * Получаем и заносим JSON запрос в request
 */
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

/**
 * Маршрутизация и обработка методов
 */
$arPost = [
    Routes::BASKET_ADD => 'addToBasket',
    Routes::OFFERS_FILTER_URL => 'getOffersList',
    Routes::REGISTRATION_CHECK_EMAIL => 'registrationCheckEmail',
    Routes::REGISTRATION => 'registration',
    Routes::REGISTRATION_FILE_UPLOAD => 'registrationFileUpload',
    Routes::OFFERS_EXCEL_UPLOAD => 'offersExcelUpload',
];

if (!empty($arPost)) {
    foreach ($arPost as $url => $method) {
        $app->post($baseURL . $url, function (Request $request) use ($app, $rest, $method) {
            return $rest->execute(
                [
                    'request' => $request,
                    'method' => $method
                ]
            );
        });
    }
}

$arGet = [

];
if (!empty($arGet)) {
    foreach ($arGet as $url => $method) {
        $app->get($baseURL . $url, function (Request $request) use ($app, $rest, $method) {
            return $rest->execute(
                [
                    'request' => $request,
                    'method' => $method
                ]
            );
        });
    }
}

$app->get('/', function () use ($data) {
    return false;
});

$app->error(function (\Exception $e, $code) use ($app) {
    $errorData = [
        "errorType" => "internal",
        "errorMessage" => "Ошибка на стороне сервера [" . $code . "]: " . $e->getMessage(),
    ];
    return $app->json($errorData, 500);
});

$app->run();
