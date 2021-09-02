# ProcessWire Emoji Textformatter Module

This module converts named shortcode emojis in ProcessWire text or textarea fields 
to the corresponding UTF-8 emoji character. For instance `:smile:` converts to a
smile 😄 emoji.

This can be especially useful if your ProcessWire uses the `utf8` character set 
rather than `utf8mb4` as it enables you to use emojis on an installation that 
typically would not be able to save them. This is because emojis usually require 
`utf8mb4` (4 bytes characters) to be used by the database in order to store them.

Note that how the emoji appears (and sometimes whether it appears) can vary from 
platform to platform, and from browser to browser. 

## Emoji reference

[Here](https://github.com/ryancramerdesign/TextformatterEmoji/blob/main/emojis.md)
is an alphabetical list of supported emojis and the shortcodes you can use 
to show them in your text. If you want to add any emojis that are not already
present you can do so in the file: 
[emojis.json](https://github.com/ryancramerdesign/TextformatterEmoji/blob/main/emojis.json)

