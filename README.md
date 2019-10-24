### English description is below.

# Плагин-блок «Надо проверить» для Moodle 3.7.1

### Описание

![Alt text](https://github.com/gitReiko/need_to_check/blob/master/readme_pic.png "Скриншот плагина")

Плагин позволяет контролировать проверку работ студентов преподавателями.

В зависимости от роли пользователя существует три основных сценария использования:
- администратор сайта или глобальный управляющий видит (контролирует) все работы, которые нужно проверить другим преподавателям;
- локальный управляющий видит (контролирует) работы других преподавателей в зоне своей ответственности. Зона ответственности определяется ролью управляющий в соответствующих курсах;
- преподаватель видит работы, которые ему нужно проверить.

Пользователи имеющие одновременно функции контролёра и преподавателя используют комбинированный сценарий.

*Техническая информация. На самом деле плагин работает с архетипами, а не ролями. Использует три архетипа: manager, editing teacher и teacher.*

Возле каждого элемента (строки) находится две цифры: черная и красная.
Чёрная цифра отображает количество работ студентов, которые нужно проверить на данный момент.
Красная количество работ, проверка которых уже просрочена, то есть непроверенные работы, которые должны были быть проверенными.

На проверку работ по умолчанию даётся 6 дней. 
Данную настройку можно изменить в глобальных настройках плагина.

Плагин не предназначен для коммуникации с преподавателями. 
Однако при наведении курсора на имя преподавателя отображаются его email и телефоны, 
если данная информация введена в профиле пользователя. 

### Установка



### Плагин разработан для нужд «Белорусского государственного экономического университета».


