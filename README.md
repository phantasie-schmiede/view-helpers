# View Helpers

Collection of useful ViewHelpers

## Installation

```bash
composer require psbits/view-helpers
```

## Development

```bash
composer install
composer qa
```

`composer qa` fixes everything that can be fixed automatically and afterwards runs all linters, the static
analysis and the test suites. The same checks run in the `CI` GitHub Actions workflow and in the `pre-commit`
hook that is installed by `composer install`.

### Quality checks for contributors

- `composer lint:all`: runs all configured lint and validation checks
- `composer test:unit` / `composer test:functional`: run the respective suite against the installed core
- `composer test:local`: runs the local (non-Docker) matrix. Without arguments it runs all local
  legs (13.4 and 14.3, each plus its lowest dependency set); with arguments it runs a single leg, e.g.
  `composer test:local -- 13.4` or `composer test:local -- 14.3 --lowest`. The
  `Build/Scripts/runTests.sh` runner pins the requested core in a generated `composer-matrix.json`
  (tracked files are never modified) and leaves `.Build` on the tested core afterwards
- `composer test:docker`: runs the Docker matrix (see "Testing with Docker" below)
- `composer test:all`: runs the local matrix plus the full Docker matrix
- `composer qa`: runs all fixes, all lint checks and the test suite

#### Code coverage

CI measures the line coverage of the unit and functional suites once per run (job
"Code coverage", TYPO3 13.4 / PHP 8.4 with pcov) and uploads the clover/cobertura/html
reports as the `coverage` artifact. Each suite's coverage must not drop below the
baseline in `Build/Quality/coverage-baseline.json` (tolerance: 0.5 points to absorb
small drift between PHP versions); the job fails when it does.

- `composer test:coverage`: runs both suites with coverage and checks the baseline
  (requires the pcov or xdebug PHP extension; without one the suites run without coverage)
- `composer test:coverage:update-baseline`: runs both suites and rewrites
  `Build/Quality/coverage-baseline.json` with the measured values (requires a coverage driver)

#### Testing with Docker

To run the test matrix on PHP versions that are not installed locally, or against a real database,
use the Docker setup in `Build/testing-docker/`:

1. Build the test images once (one per supported PHP version): `composer docker:images`
2. Run the whole Docker matrix: `composer test:docker` (8 legs: for 13.4 and 14.3 each -
   sqlite, sqlite-lowest, MariaDB and PostgreSQL)
3. Run a single leg: `composer test:docker -- <core-version> [--lowest] [--db sqlite|mysql|postgres]`,
   e.g. `composer test:docker -- 13.4 --db postgres`. The `--db` flag selects the compose service
   that carries the database connection settings.
4. Switch the PHP version with `PHP_VERSION=8.3 composer test:docker -- 13.4` (default is 8.4).

Every matrix run announces each leg and each step (matrix environment, composer update, unit
tests, functional tests) with a prominent banner - colored green/red for pass/fail when run in
a terminal - keeps running after a failed leg, and ends with a summary table of all legs; the
exit code is non-zero if any leg failed.

Without Docker, `runTests.sh` also accepts `--db mysql|postgres` and reads the connection settings
from the `typo3Database*` environment variables (host, port, username, password, name) - see the
script header for the defaults.

The CI workflow also publishes the extension to TER when a semver tag is pushed. Publishing requires
the `TYPO3_API_TOKEN` repository secret.
