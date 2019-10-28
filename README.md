### English description is below.

***

# Плагин-блок «Надо проверить» для Moodle 3.7.1

### Плагин разработан для нужд «Белорусского государственного экономического университета».

### Описание

![Alt text](https://github.com/gitReiko/need_to_check/blob/master/readme_pic.png "Скриншот плагина")

Плагин позволяет контролировать проверку работ студентов преподавателями.

Плагин умеет работать только с заданиями (assign), тестами (quiz) и форумами-заданиями (forum).

В зависимости от роли пользователя существует три основных сценария использования:
- администратор сайта или глобальный управляющий видит (контролирует) все работы, которые нужно проверить другим преподавателям;
- локальный управляющий видит (контролирует) работы других преподавателей в зоне своей ответственности. Зона ответственности определяется ролью управляющий в каких-либо курсах;
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
если данная информация введена в профиль пользователя.

### Установка

1. Поместить все файлы плагина в папку need_to_check;
2. Поместить папку need_to_check в папку "путь до moodle/blocks/";
3. Зайти в Moodle под админской учётной запись;
4. Установить плагин на открывшейся странице;
5. Добавить блок «Надо проверить» на любую страницу сайта (через которую плагин будет использоваться).

*Внимание. Папка с плагином должна называться именно need_to_check и находиться в папке "путь до moodle/blocks/", иначе плагин не установится.
При клонировании через консоль папка называется именно так. Но при скачивании по другому.*

### Использование

Обычным пользователям (не админам) нужно просто зайти на страницу, на которой размещён блок.

Для администраторов есть одна тонкость. Для более быстрой работы блок хранит некоторую информацию в базе данных.
Это накладывает отпечаток на использование плагина.

**Для корректной работы необходимо обновлять информацию в базе данных после изменения состава учителей, проверяющих работы студентов.**
**Это могут делать только администраторы сайта на странице с блоком.**

*Преподаватель считается проверяющим, если он зачислен в ту же группу, что и студент, а также имеет роль на основе архетипа teacher или editingteacher в соответствующей активности или курсе.*

### Скорость работы

Скорость загрузки страницы с блоком зависит от сценария использования.
Преподаватели и локальные управляющие загружают страницу практически мгновенно.
Загрузка страницы глобального управляющего или администратора может занять много времени.

Скорость загрузки зависит от количества работ, которые нужно проверить.
Чем меньше работ на проверку тем быстрее загружается страница.

Кроме того операция внесения информации в базу данных (нужна для ускорения работы) занимает больше времени, чем простая загрузка страница.
Внесение информации в базу данных осуществляется при первой загрузке страницы с блоком или ручном запуске операции администратором.

### Сторонние зависимости

Отсутствуют.

### Автор

Маковский Денис Анатольевич khornau@gmail.com

***

# Block type plugin «Need to check» for Moodle 3.7.1

### Plugin was developed for «Belarusian State Economic University».

### Description

![Alt text](https://github.com/gitReiko/need_to_check/blob/master/readme_pic.png "Plugin screenshot")

Plugin allows to control check of student work by teachers.

Plugin can only work with assign, quiz and forum.

Depending on the role of user, there are three main usage scenario:
- site administrator or global manager sees (controls) all works that needs to be checked by other teachers;
- local manager sees (controls) works of other teachers in his area of responsibility. Area of responsibility is determined by the role of the manager in any courses;
- teacher sees works that he needs to check.

Users with both supervisor and teacher functions use a combined usage scenario.

*Technical information. In fact, plugin works with archetypes, not roles. Uses three archetypes: manager, editing teacher and teacher.*

Near each element (line) is two numbers: black and red.
Black number displays the number of students' works that need to be checked at the moment.
Red number indicates the number of student work that should have already been checked.

By default, for work check is given 6 days. 
This setting can be changed in the global settings of the plugin.

The plugin is not intended for communication with teachers.
However, when you hover mouse over the teacher’s name, his email and phone numbers are displayed,
if this information is entered in the user profile.

### Install

1. Put all plugin files in a folder need_to_check;
2. Put the need_to_check folder in the folder "path to moodle/blocks/";
3. Go to Moodle under the admin account;
4. Install the plugin on the page that opens;
5. Add block «Need to check» on any page of the site (through which the plugin will be used).

*Attention. Folder with the plugin should be called exactly need_to_check and be in the folder "path to moodle/blocks/", otherwise the plugin will not install.
When cloning through the console, the folder is named that way. But when downloading is named differently.*

### Using

Ordinary users (not admins) just need to go to the page on which the block is placed.

For administrators, there is one subtlety. For faster work, block stores some information in the database.
This leaves an imprint on the use of plugin.

**For correct work, it is necessary to update the information in the database after changing the composition of teachers who check students' work.**
**This can only be done by site administrators on the page with the block.**

*The teacher is considered to be checking if he is enrolled in the same group as the student, and also has a role based on the archetype of teacher or editingteacher in the corresponding activity or course.*

### Work speed

The speed of loading a page with a block depends on the usage scenario.
Teachers and local managers load the page almost instantly.
It can take a long time to load the global manager or administrator page.

Download speed depends on the number of works to check.
Fewer works teachers need to check, faster page loads.

In addition, operation of entering information into database (needed to speed up work) takes longer time, than a simple page loading.
Operation of entering information into database is carried out of the first time page with block load or manual operation by administrator.

### Third-party dependencies

Are absent.

### Author

Makouski Denis Anatolievich khornau@gmail.com
