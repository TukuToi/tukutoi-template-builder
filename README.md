# TukuToi Template Builder
 TukuToi Template Builder allows you to create any kind of Template for your WordPress or ClassicPress website, directly from within the Admin area, without editing PHP Files.

## Changelog

### 1.8.9
[Fixed] Uninstall checked on wrong plugin slug

### 1.8.7
[Fixed] Undefined Global Header

### 1.8.6
[Fixed] Missing Text Domains and some Comments for CPCS Review.

### 1.8.5
[Fixed] Template ShortCode not parsing nested ShortCodes
[Fixed] funktion ShortCode wrongly passing arguments with '' apostrophes

### 1.8.3
* [Fixed] bootstrap navbar shortcode unused argument

### 1.8.2
* [Fixed] several undefined array offsets
* [Fixed] ability to delete last assignement in templates select2

### 1.8.0
* [Fixed] several pontentially undefined $variables
* [Fixed] array offsets  undefined
* [Fixed] revert basename Constant

### 1.7.0
* [Fixed] global $post was mistakenly compared to an array
* [Added] Global instead of hardcoded version and basename

### 1.6.0
* [Added] Support for granular Post and Taxonomy Archives/Single templates where applicable
* [Added] OptGroups to Template Selector for easier organization
* [Added] Support for Date Archives (All, Year, Month, Day)
* [Changed] Some general improvements to GUI and design
* [Added] PHP Minimal Requirements: PHP 7.0.0

### 1.5.0
* [Added] Navigation Menu ShortCode (With optional Bootstrap 4 Navwalker)
* [Added] Sidebar ShortCode
* [Added] Widget ShortCode

### 1.4.0
* [Fixed] Select2 Placeholders are now specific
* [Added] Filter to allow non-public, or delisted Post Types in the "Content Template" Selector. Use `tkt_tmplt_bldr_supported_post_types`, pass an array where $key => $post_type_object
* [Changed] Few GUI improvements


### 1.3.0
* [Added] A totally nutters feature: do_action, function, and add_filters with ShortCodes. This helps building headers and footers, amongst other things.
* [Added] `template`, `do_action` and `funktion` ShortCodes (see also above [Added])
* [Added] Template Admin Columns for header, footer, parent and assigned templates
* [Fixed] "Copy" ShortCode produced shortcodes without prefix
* [Fixed] Merge ShortCode and Type Declarations instead of overwriting them

### 1.2.0
* [Added] Possibility to apply template to the content only (Content Template)
* [Added] Possibility to apply Global Header and Global Footer

### 1.1.0
* [Added] Backend Template Settings (Assigned Template, Header, Footer)
* [Added] Select2 Settings instead of plain vanilla
* [Added] Mechanism to save options and Template Settings
* [Changed] Improved logic to route the Templates (also performance related)
* [Changed] Added extensive comment about the escaping problematic of unfiltered content 
* [Removed] Comments settings metabox (comments stay, settings for pingbacks thou are useless)

### 1.0.0
* [Added] Templating system
* [Added] Backend Template Editor with CodeMirror
* [Added] Public Filters to control templates 

### 0.0.1
* [Added] Initial Plugin Commit