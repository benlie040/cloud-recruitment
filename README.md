# cloud-recruitment

This PHP project is part of the recruitment process at a company I'm applying to.
The task is to create different PHP scripts, for analyzing an nginx access logfile to gather
a few statistics and determine issues related to the UTM licences of the client servers.

## Roadmap

- **`Task #1`**: Identify the ten serial numbers, which are accessing the server the most and their amount of requests.
- **`Task #2`**: Identify the ten serial numbers, which are installed on more than one device and violate this rule the most.
- **`Task #3`** (optional): Identify the different classes of hardware and provide the number of licenses active on these classes.

## Usage

**Task #1**

- go into the src folder with `cd src`
- run the script with the command `php utm_licences.php`
- the result will be stored in `data/result_utm_licences.php.txt`

**Task #2**

- go into the src folder with `cd src`
- run the script with the command `php utm_licence_violation.php`
- the result will be stored in `data/result_utm_licence_violation.txt`
