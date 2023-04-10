# fruityvice task by Sofien Benrhouma

## Task description
> Write a console script for getting all fruits from https://fruityvice.com/ and saving them into local DB (MySQL or PostgreSQL).
>
> We will run this command from terminal, for example:
>
> php bin/console fruits:fetch
> 
>When all fruits are saved into the DB send an email about it to a dummy admin email (e.g. test@gmail.com or your gmail address).
> 
> Create a page with all fruits (paginated). Add a form to filter fruits by name and family. Each fruit can be added to favorites (up to 10 fruits).
> 
> Create a page with favorite fruits. Display all nutrition facts for all fruits.
> 
> Add a README file with installation and startup instructions.
> 
> Treat the task as a full-fledged project. In php follow PSR-12, in JS follow JavaScript Standard Style. Unit tests are welcome. You should use the Symfony PHP framework for backend and VueJS (TypeScript optional) for frontend.
> 
> Once the project code is ready please upload it to a github repo and share with us the githup repo URL.

## Installation
Follow below guide to setup and use the application.

**Note:** need to have symfony cli, yarn and composer are already installed.

1. Open and edit .env file. two important values should be changed: `DATABASE_URL` and `MAILER_DSN`
2. Run below commands to install dependencies:
    ```bash
    $ composer install
    $ yarn install
    $ php bin/console doctrine:migrations:migrate
    ```
Enter `Yes` in the last command's prompt

3. Run below command to fetch data from fruityvice.com

    `$ php bin/console app:fetch`



4. Run below commands in separated terminals:
    ```bash
    $ symfony server:start
    $ yarn encore dev-server
    ```
5. Access the project using `http://localhost:8000`

## Short description

The fetch part is written as a symfony command to achieve more consistency. beside this, vue as added to the project via symfony supported guide.

As html tables are more neat to preview these data, a table mode is added but to achieve responsive design, the default mode is using cards.

element-plus is used as a UI framework.

As the task description was dispute about how filters should work, there are two versions of filters. one set will search through data and the other will let user select whatever they want.


