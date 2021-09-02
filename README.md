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
docker build -t fee_calc_app .
```

Next step is to install app dependencies. Run composer update in the container to get your copy of the app setup:
```bash
docker run -it \
--rm --volume=$(pwd):/var/www/fee-calculator:z \
fee_calc_app \
sh -c "composer install"
```

## Running application

To run the fee calculation script go to the root directory of the project 
and run the command in the command line:

```bash
docker run -it \
  --rm --volume=$(pwd):/var/www/fee-calculator:z \
  fee_calc_app \
  sh -c "php -f public/script.php etc/input.csv"
```

Feel free to modify `etc/input.csv` or use alternative input file.

Run the following command from command line to run the unit and functional tests:
```bash
docker run -it \
  --rm \
  --volume=$(pwd):/var/www/fee-calculator:z \
  fee_calc_app \
  sh -c "composer test"
```

## Additional info

### Changes made to the initial app skeleton:
 - removed bcmath  extension as do not use it;
 - added json extension for parsing responses from API
 - restricted version of PHP to 7.4(and therefore had to update phpcsfixer version from 2.x to 3.x and its config)

### Essential ToDo's as of 02/09/2021:
 - create custom validator for transaction entity
 - finish up some unit test for most important classes

Happy testing :)
