## Prerequisites
#### You need to have installed on your machine:

- Composer : https://getcomposer.org/
- Docker : https://www.docker.com/

## Usage
1. Clone the repository:
```
git clone git@github.com:tania0808/todo-p8.git
```

2. Configure your environment variables and yout database
```
DATABASE_URL=
```

3. Run the application:
```
composer install
docker-compose up
symfony server:start -d
```
4. Create the database:
```
php bin/console doctrine:database:create
```
5. Run the migrations:
```
php bin/console doctrine:migrations:migrate
```

6. Load the fixtures:
```
php bin/console doctrine:fixtures:load
```