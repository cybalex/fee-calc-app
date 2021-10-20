# Commission fee calculator app

## About the task

Some bank allows private and business clients to `deposit` and `withdraw` funds to and from accounts in multiple currencies. Clients may be charged a commission fee.

You have to create an application that handles operations provided in CSV format and calculates a commission fee based on defined rules.

## Commission fee calculation
- Commission fee is always calculated in the currency of the operation. For example, if you `withdraw` or `deposit` in US dollars then commission fee is also in US dollars.
- Commission fees are rounded up to currency's decimal places. For example, `0.023 EUR` should be rounded up to `0.03 EUR`.

### Deposit rule
All deposits are charged 0.03% of deposit amount.

### Withdraw rules
There are different calculation rules for `withdraw` of `private` and `business` clients.

**Private Clients**
- Commission fee - 0.3% from withdrawn amount.
- 1000.00 EUR for a week (from Monday to Sunday) is free of charge. Only for the first 3 withdraw operations per a week. 4th and the following operations are calculated by using the rule above (0.3%). If total free of charge amount is exceeded them commission is calculated only for the exceeded amount (i.e. up to 1000.00 EUR no commission fee is applied).

For the second rule you will need to convert operation amount if it's not in Euros. Please use rates provided by [api.exchangeratesapi.io](https://api.exchangeratesapi.io/latest).


## Requirements

Docker is required to run the app. Follow the installation steps
as documented [here](https://docs.docker.com/engine/install/centos/) 
for your operating system specifically.

## Installation

Make sure docker is installed on the machine. Run the following command from command line to ensure that
```bash
docker --version
```
The app is supposed to be run inside a docker container.
To build the container, go to the root path of the project and 
run the following command in the command line:
```bash
docker build -t fee_calc_app ./docker/fpm
```

Next step is to install dependencies. Run composer install inside the fpm container:
```bash
docker run -it \
--rm --volume=$(pwd):/var/www/fee-calculator:z \
fee_calc_app \
sh -c "composer install"
```

Create a docker network for the application:
```bash
docker network create fee-calc-app;
```

The next step is to populate **.env** file:
```bash
cp .env.dist .env
```
Make sure all the variables are properly configures. Pay attention to ports. Check if the port provided for mysql
is not used.

Run mysql container as a process on the background:
```bash
docker run -d \
  --env-file ./.env \
  --network=fee-calc-app \
  -p $(. ./.env; echo "$MYSQL_PORT"):3306 \
  --volume=$(pwd)/docker/mysql/data/:/var/lib/mysql:z  \
  --hostname=$(. ./.env; echo "$MYSQL_HOST") \
  --name=fee_calc_app_mysql \
  --restart=unless-stopped \
  mysql:8
```

## Running application

Run fee calculation app by executing cmd command in the root directory of the project:
```bash
docker run -it \
  --env-file ./.env \
  --rm --volume=$(pwd):/var/www/fee-calculator:z \
  --network=fee-calc-app \
  fee_calc_app \
  sh -c "composer run-app"
```
*etc/input.csv* file will be used to run the app.


To run the fee calculation script with a custom input csv file, run:
```bash
docker run -it \
  --env-file ./.env \
  --rm --volume=$(pwd):/var/www/fee-calculator:z \
  --network=fee-calc-app \
  fee_calc_app \
  sh -c "php -f public/script.php fee.calculate --file=etc/input.csv"
```

Feel free to modify `etc/input.csv` or use alternative input file to test different transaction sequences 
and different scenarios.

In case the *fee.calculate* command is not provided with the *--file* option, location of a csv file 
will be prompted in the console.

## Running tests

Run unit and functional tests:
```bash
docker run -it \
  --env-file ./.env \
  --rm \
  --volume=$(pwd):/var/www/fee-calculator:z \
  --network=fee-calc-app \
  -e MYSQL_DATABASE \
  -e MYSQL_USER \
  -e MYSQL_PASSWORD \
  -e MYSQL_HOST \
  fee_calc_app \
  sh -c "composer test"
```

Run tests with coverage by executing the following command from CLI:
```bash
docker run -it \
  --rm \
  --env-file ./.env \
  --volume=$(pwd):/var/www/fee-calculator:z \
  --network=fee-calc-app \
  -e MYSQL_DATABASE \
  -e MYSQL_USER \
  -e MYSQL_PASSWORD \
  -e MYSQL_HOST \
  -e MYSLQ_PORT \
  fee_calc_app \
  sh -c "composer test-coverage"
```
Coverage report will be available under `var/reports/coverage` directory.

## Cleanup / Uninstall
Stop any running fpm docker containers, which use fee_calc_app image (Use `docker attach` if any are run on the background)
Then run the following commands:
```bash
    docker rm --force fee_calc_app_mysql; \
    docker network rm fee-calc-app; \
    docker rmi fee_calc_app; \
    rm -rf vendor\*; \
    rm -rf docker\mysql\data\*; \
    rm -rf var\log\logs.txt
```
This will remove the fpm image, destroy the docker network, clear temporary files like logs and also removes vendors
and database data
