# Search
[![Build Status](https://travis-ci.org/trendwerk/search.svg?branch=master)](https://travis-ci.org/trendwerk/search) [![codecov](https://codecov.io/gh/trendwerk/search/branch/master/graph/badge.svg)](https://codecov.io/gh/trendwerk/search)

Basic extensions for searching in WordPress. Currently only supports searching in `postmeta`.

Quick links: [Install](#install) | [Usage](#usage) | [Dimensions](#dimensions) | [Example](#example)

_Note: This basic extension is not very scalable and meant for smaller databases. This package could get slow for complex meta searches. In that case, [Elasticsearch](https://www.elastic.co/) would be a better solution._

## Install
```sh
composer require trendwerk/search
```

## Usage

1. [Initialize package](#initialize)
2. [Add search dimension(s)](#dimensions)

### Initialize

```php
$search = new \Trendwerk\Search\Search();
$search->init();
```

This code should be run when bootstrapping your theme.

### Dimensions
Currently this package only supports metadata as a search dimension. Dimensions can be added by using `addDimension`:

```php
$search->addDimension($dimension);
```

| Parameter | Default | Required | Description |
| :--- | :--- | :--- | :--- |
| `$dimension` | `null` | Yes | Should be an instance of a class that implements [`Dimension\Dimension`](https://github.com/trendwerk/search/blob/master/src/Dimension/Dimension.php).

### Meta
```php
$metaDimension = new \Trendwerk\Search\Dimension\Meta([
	'key' => 'firstName',
]);

$search->addDimension($metaDimension);
```

Available options for constructing an instance of `Meta`:

| Parameter | Default | Required | Description |
| :--- | :--- | :--- | :--- |
| `key` | `null` | Yes | The `meta_key` to search for
| `compare` | `=` | No | The database comparison that should be made for the meta key. Currently supports `LIKE` and `=`. When using `LIKE`, make sure to include a percent symbol (`%`) in your `key` parameter as a wildcard. See [Example](#example)

### Terms
```php
$search->addDimension(new \Trendwerk\Search\Dimension\Term($wpdb, [
	'taxonomy' => 'taxonomyName',
]));
```

Available options for constructing an instance of `Term`:

| Parameter | Default | Required | Description |
| :--- | :--- | :--- | :--- |
| `taxonomy` | `null` | Yes | The `taxonomy` which terms should be included in search

## Example

```php
use Trendwerk\Search\Dimension\Meta;
use Trendwerk\Search\Dimension\Term;
use Trendwerk\Search\Search;

$search = new Search();
$search->init();

$search->addDimension(new Meta($wpdb, [
    'compare' => 'LIKE',
    'key'     => 'lastNames%',
]));

$search->addDimension(new Meta($wpdb, [
    'key' => 'firstName',
]));

$search->addDimension(new Term($wpdb, [
    'taxonomy' => 'category',
]));
```
