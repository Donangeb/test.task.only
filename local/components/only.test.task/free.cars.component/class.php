<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;

class FreeCarsComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        global $USER;

        if (!$USER->IsAuthorized()) {
            ShowError("Требуется авторизация");
            return;
        }

        Loader::includeModule('iblock');
        Loader::includeModule('highloadblock');

        // Получаем параметры времени из GET
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;

        if (!$start || !$end) {
            $this->arResult['ERROR'] = "Не указано время поездки";
            $this->arResult['CARS'] = [];
            $this->includeComponentTemplate();
            return;
        }

        // Получаем должность пользователя и доступные категории комфорта
        $userId = $USER->GetID();
        $user = CUser::GetByID($userId)->Fetch();
        
        // Получаем должности пользователя (множественное поле)
        $positionIds = $user['UF_POSITION'];
        if (empty($positionIds)) {
            $this->arResult['ERROR'] = "У пользователя не указана должность";
            $this->arResult['CARS'] = [];
            $this->includeComponentTemplate();
            return;
        }
        if (!is_array($positionIds)) {
            $positionIds = [$positionIds];
        }
        $positionIds = array_filter($positionIds);
        if (empty($positionIds)) {
            $this->arResult['ERROR'] = "У пользователя не указана должность";
            $this->arResult['CARS'] = [];
            $this->includeComponentTemplate();
            return;
        }

        // Получаем данные должностей из хайлоадблока
        $hlblock = HL\HighloadBlockTable::getById('2')->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();

        $arCategories = [];
        $rsData = $entityClass::getList([
            'filter' => ['ID' => $positionIds],
            'select' => ['UF_COMFORT_CATEGORIES']
        ]);

        while ($arItem = $rsData->fetch()) {
            if (is_array($arItem['UF_COMFORT_CATEGORIES'])) {
                $arCategories = array_merge($arCategories, $arItem['UF_COMFORT_CATEGORIES']);
            } elseif ($arItem['UF_COMFORT_CATEGORIES']) {
                $arCategories[] = $arItem['UF_COMFORT_CATEGORIES'];
            }
        }

        $arCategories = array_unique(array_filter($arCategories));

        if (empty($arCategories)) {
            $this->arResult['ERROR'] = "Нет доступных категорий для вашей должности";
            $this->arResult['CARS'] = [];
            $this->includeComponentTemplate();
            return;
        }

        // Получаем автомобили нужных категорий
        $arCars = [];
        $arCarIds = [];
        $rsCars = CIBlockElement::GetList([], [
            'IBLOCK_CODE' => 'IBLOCK_CARS',
            'ACTIVE' => 'Y',
            'PROPERTY_CATEGORY_NAME' => $arCategories
        ], false, false, ['ID', 'NAME', 'PROPERTY_MODEL_NAME', 'PROPERTY_CATEGORY_NAME', 'PROPERTY_DRIVER_NAME']);
        while ($arCar = $rsCars->Fetch()) {
            $arCars[$arCar['ID']] = $arCar;
            $arCarIds[] = $arCar['ID'];
        }
        if (empty($arCarIds)) {
            $this->arResult['ERROR'] = "Нет доступных автомобилей";
            $this->arResult['CARS'] = [];
            $this->includeComponentTemplate();
            return;
        }
        $end = date('Y-m-d H:i:s', strtotime($end));
        $start = date('Y-m-d H:i:s', strtotime($start));
        // Получаем занятые автомобили на это время
        $arBookingFilter = [
            'IBLOCK_CODE' => 'IBLOCK_CAR_BOOKINGS', // используйте ID инфоблока!
            'ACTIVE' => 'Y',
            'PROPERTY_CAR' => $arCarIds,
            // Пересечение интервалов
            [
                "LOGIC" => "AND",
                ["<PROPERTY_START" => $end],
                [">PROPERTY_END" => $start],
            ]
        ];
        $rsBookings = CIBlockElement::GetList([], $arBookingFilter, false, false, ['ID', 'PROPERTY_CAR']);
        $arBusyCarIds = [];
        while ($arBooking = $rsBookings->Fetch()) {
            $arBusyCarIds[] = $arBooking['PROPERTY_CAR_VALUE'];
        }
        // Оставляем только свободные автомобили
        $arFreeCars = [];

        foreach ($arCars as $carId => $car) {
            if (!in_array($carId, $arBusyCarIds)) {
                $car['MODEL_NAME'] = '';
                $car['CATEGORY_NAME'] = '';
                $car['DRIVER_NAME'] = '';

                if ($car['PROPERTY_MODEL_NAME_VALUE']) {
                    $model = CIBlockElement::GetByID($car['PROPERTY_MODEL_NAME_VALUE'])->GetNext();
                    if ($model) {
                        $car['MODEL_NAME'] = $model['NAME'];
                    }
                }
                if ($car['PROPERTY_CATEGORY_NAME_VALUE']) {
                    $category = CIBlockElement::GetByID($car['PROPERTY_CATEGORY_NAME_VALUE'])->GetNext();
                    if ($category) {
                        $car['CATEGORY_NAME'] = $category['NAME'];
                    }
                }
                if ($car['PROPERTY_DRIVER_NAME_VALUE']) {
                    $driver = CIBlockElement::GetByID($car['PROPERTY_DRIVER_NAME_VALUE'])->GetNext();
                    if ($driver) {
                        $car['DRIVER_NAME'] = $driver['NAME'];
                    }
                }

                $arFreeCars[] = $car;
            }
        }

        $this->arResult['CARS'] = $arFreeCars;

        // Обработка аренды (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['CAR_ID']) && check_bitrix_sessid()) {
            $carId = intval($_POST['CAR_ID']);
            $startRaw = $_POST['start'] ?? null;
            $endRaw = $_POST['end'] ?? null;
            $start = $startRaw ? date('d.m.Y H:i:s', strtotime($startRaw)) : null;
            $end = $endRaw ? date('d.m.Y H:i:s', strtotime($endRaw)) : null;

            if ($start && $end && in_array($carId, array_column($arFreeCars, 'ID'))) {
                $el = new CIBlockElement;
                $arFields = [
                    "IBLOCK_CODE" => 'IBLOCK_CAR_BOOKINGS',
                    "NAME" => "Бронирование автомобиля " . $carId . " пользователем " . $userId,
                    "ACTIVE" => "Y",
                    "PROPERTY_VALUES" => [
                        "CAR" => $carId,
                        "USER" => $userId,
                        "START" => $start,
                        "END" => $end,
                    ],
                ];
                $bookingId = $el->Add($arFields);
                if ($bookingId) {
                    $this->arResult['SUCCESS'] = "Автомобиль успешно забронирован!";
                } else {
                    $this->arResult['ERROR'] = "Ошибка бронирования автомобиля: " . $el->LAST_ERROR;
                }
            } else {
                $this->arResult['ERROR'] = "Некорректные данные для бронирования";
            }
        }

        $this->includeComponentTemplate();
    }
}
