## 1. Создание инфоблока

Создайте новый тип инфоблока с названием **«Бронирование автомобиля»**.

### В типе инфоблоков добавьте следующие инфоблоки:

- **Автомобиль**
Инфоблок:
  ![image](https://github.com/user-attachments/assets/3a252f2b-8f00-427e-a75c-3227c545cade)
Свойства:
![image](https://github.com/user-attachments/assets/ebf17407-bed2-4726-97ea-8ea4d19ec45b)
- **Бронирование**
Инфоблок:
![image](https://github.com/user-attachments/assets/a7385229-a484-45b3-af93-b279571c639e)
Свойства: 
![image](https://github.com/user-attachments/assets/c80677df-b55f-4912-b705-27ae0e668fb2)
- **Водитель**
Инфоблок:
![image](https://github.com/user-attachments/assets/c9606a84-f244-456d-a62f-3a7cbd64ef94)
Свойства:
![image](https://github.com/user-attachments/assets/76613c54-9afc-4b32-8469-f01e80648bfb)
- **Категория комфорта**
Инфоблок:
![image](https://github.com/user-attachments/assets/18e7d292-5571-4bd7-861a-e3a878f1adc6)
Свойства:
Ничего ненужно
- **Модель автомобиля**
Инфоблок:
![image](https://github.com/user-attachments/assets/4d1a5215-5054-40d5-be21-cf13139097a3)
Свойства:
![image](https://github.com/user-attachments/assets/231d6ea2-5ead-4e3f-af82-4217ee245cec)
> **Примечание:**  
> Для свойства «Категория комфорта» дополнительных настроек не требуется.

---

## 2. Структура инфоблоков

- Тип инфоблока «Бронирование автомобиля»  
  - Инфоблоки:  
    - Автомобиль
    - Бронирование
    - Водитель  
    - Категория комфорта
    - Модель автомобиля
---

## 3. Добавление Highload-блока

Создайте Highload-блок с названием **«Должность»** (`Post`).

- **Важно:**  
  Сохраните ID созданного Highload-блока (например, `HLBLOCK_2`).

### В Highload-блоке добавьте следующие поля:

- `UF_NAME_POST` — название должности
![image](https://github.com/user-attachments/assets/b53bd60f-52e4-469e-9743-2fefdea5707b)

- `UF_COMFORT_CATEGORIES` — категории комфорта
![image](https://github.com/user-attachments/assets/50125e44-6914-4d7e-9ba2-c8d53900b59a)


---

## 4. Добавление пользовательских полей

У пользователя добавьте дополнительные поля:

- `UF_POSITION` — должность
![image](https://github.com/user-attachments/assets/1815f818-6838-4f33-b758-a54541bb9b82)

---

## 5. Изменения в class.php

В файле `class.php`:

- Найдите переменную `$hlblock`
- Замените значение `2` на последние цифры ID вашего Highload-блока
