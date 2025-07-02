<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="free-cars-component">
    <h2>Бронирование автомобиля</h2>

    <!-- Форма выбора времени -->
    <form method="get">
                    <label for="start-time">Начало поездки</label>
                    <input type="datetime-local"
                        id="start-time"
                        name="start"
                        class="form-control"
                        value="<?= htmlspecialchars($_GET['start'] ?? '') ?>"
                        required>
                    <label for="end-time">Окончание поездки</label>

                    <input type="datetime-local"
                        id="end-time"
                        name="end"
                        class="form-control"
                        value="<?= htmlspecialchars($_GET['end'] ?? '') ?>"
                        required>
                        
        <button type="submit" class="btn btn-primary">Найти свободные автомобили</button>
    </form>

    <?php if ($arResult['ERROR']): ?>
        <div><?= $arResult['ERROR'] ?></div>
    <?php endif; ?>

    <?php if ($arResult['SUCCESS']): ?>
        <div><?= $arResult['SUCCESS'] ?></div>
    <?php endif; ?>

    <?php if (!empty($arResult['CARS'])): ?>
        <? $this->arResult['START'] = $start;
        $this->arResult['END'] = $end; ?>
        <h3>Доступные автомобили на период:</h3>
        <p class="text-muted">
            <?= FormatDate("j F Y H:i", MakeTimeStamp(date('d.m.Y H:i:s', strtotime($_GET['start'])))) ?> -
            <?= FormatDate("j F Y H:i", MakeTimeStamp(date('d.m.Y H:i:s', strtotime($_GET['end'])))) ?>
        </p>

        <?php foreach ($arResult['CARS'] as $car): ?>
            <h5><?= $car['NAME'] ?></h5>
            <p><strong>Модель:</strong> <?= $car['MODEL_NAME'] ?></p>
            <p><strong>Категория комфорта:</strong> <?= $car['CATEGORY_NAME'] ?></p>
            <p><strong>Водитель:</strong> <?= $car['DRIVER_NAME'] ?: 'Не назначен' ?></p>

            <form method="post" class="mt-3">
                <?= bitrix_sessid_post() ?>
                <input type="hidden" name="CAR_ID" value="<?= $car['ID'] ?>">
                <input type="hidden" name="start" value="<?= htmlspecialchars($_GET['start']) ?>">
                <input type="hidden" name="end" value="<?= htmlspecialchars($_GET['end']) ?>">
                <button type="submit" class="btn btn-primary">Забронировать</button>
            </form>
        <?php endforeach; ?>
    <?php elseif ($_GET['start'] && $_GET['end']): ?>
        <div class="alert alert-info">Нет доступных автомобилей на выбранное время</div>
    <?php endif; ?>
</div>