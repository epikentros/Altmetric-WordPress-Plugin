# Altmetric WordPress Plugin — Community Fork

> **This is an unofficial community fork** of the original [Altmetric WordPress Plugin](https://github.com/altmetric/wordpress-plugin), which is no longer maintained by Altmetric.
> This fork fixes PHP 8.x compatibility issues that caused a fatal error on activation.
> All original functionality is preserved.

---

# Altmetric WordPress Plugin

The Altmetric WordPress plugin provides [Altmetric embedded badge](http://api.altmetric.com/embeds.html) support to your WordPress blog.

## Features

* Includes the Altmetric badge Javascript automatically (i.e. your posts will contain the following javascript)

<pre><code>&lt;script type='text/javascript' src='https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js'&gt;&lt;/script&gt;</code></pre>

* Ability to include an embedded Altmetric badge for any article by referring to
  its [DOI](http://en.wikipedia.org/wiki/Digital_object_identifier), [arXivID](http://arxiv.org/help/arxiv_identifier),
  [PubMed ID](http://www.ncbi.nlm.nih.gov/pmc/pmctopmid/) or similar identifier

## Installation

### Via WordPress Admin (recommended)

1. Download the latest release ZIP from the [Releases](../../releases) page
2. In your WordPress dashboard, go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP file and click **Install Now**
4. Click **Activate Plugin**

### Via FTP

1. Copy `altmetric-embeds.php` to your `/wp-content/plugins/` directory
2. Navigate to the **Plugins** dashboard page
3. Click **Activate** for the "Altmetric embeds" plugin

## Usage

Once the plugin is activated, you can add Altmetric embedded badges to your blog posts using shortcodes.

### Article identifiers

The only required attribute is an article identifier. Supported identifiers:

#### DOI:

    [altmetric doi="10.1038/nature.2012.9872"]

#### arXiv:

    [altmetric arxiv_id="1209.4191"]

#### PubMed ID:

    [altmetric pmid="21771119"]

#### Handle:

    [altmetric handle="2022/14471"]

### Type

The `type` attribute controls which badge style to display. Available values: `donut`, `medium-donut`, `large-donut`, `1`, `4`.

See the [badge types documentation](http://api.altmetric.com/embeds.html#badge-types) for a visual reference.

    [altmetric doi="10.1038/nature.2012.9872" type="large-donut"]

### Popovers

    [altmetric doi="10.1038/nature.2012.9872" popover="right"]

Valid values: `left`, `right`, `top`, `bottom`.

### Details

    [altmetric doi="10.1038/nature.2012.9872" details="right"]

Note: `details` can only be placed on the right. You cannot combine `popover` and `details` — `popover` takes priority.

### Float

    [altmetric doi="10.1038/nature.2012.9872" float="right"]

Valid values: `left`, `right`, `none`.

### Hide when no mentions

    [altmetric doi="10.1038/nature.2012.9872" hide_no_mentions="true"]

### Combined example

    [altmetric doi="10.1038/nature.2012.9872" float="right" popover="left" style="box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);" class="someclass" type="1"]

This embeds a 110×20px badge, floated right, with a popover on the left, a box shadow, and a custom CSS class.

## License

Licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

## Changelog

### 0.0.8 (2025)

* **PHP 8.x compatibility fix**: declared all class methods as `static` to prevent fatal errors when used as WordPress filter/shortcode callbacks. The original code used non-static methods with a class-name string callback (`array('Altmetric', 'method')`), which was deprecated in PHP 7.x and causes a fatal error in PHP 8.x.
* Forked and maintained by [@epikentros](https://github.com/epikentros)

### 0.0.7 (20th May 2013)

* readme.txt added

### 0.0.6 (14th May 2013)

* Added `example` attribute for embedding shortcode examples alongside the actual badge

### 0.0.5 (14th May 2013)

* Moved `altmetric-embeds.php` to top level so that tag ZIPs from GitHub work

### 0.0.4 (7th May 2013)

* Fix `float` attribute to not ignore other styles

### 0.0.3 (3rd May 2013)

* Add `details` attribute

### 0.0.2 (3rd May 2013)

* Add shortcode functionality

### 0.0.1 (1st May 2013)

* Official release

## Credits

Originally created by [Will Roe](http://www.digital-science.com/people/william-roe) at [Altmetric](https://www.altmetric.com).
Maintained in this fork by [@epikentros](https://github.com/epikentros).
