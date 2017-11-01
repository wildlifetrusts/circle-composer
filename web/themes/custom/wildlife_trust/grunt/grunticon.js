/**
 * Grunticon icon generation.
 *
 * - https://github.com/filamentgroup/grunticon
 */
module.exports = {
  icons: {
    files: [{
      expand: true,
      cwd: 'assets/icons/minified',
      src: ['*.svg', '*.png'],
      dest: "icons"
    }],
    options: {
      enhanceSVG: true,
      datasvgcss: "../css/grunticon-data-svg.css",
      datapngcss: "../css/grunticon-data-png.css",
      urlpngcss: "../css/grunticon-png-fallback.css",
      pngfolder: "../icons/png/",
      colors: {
        white: '#ffffff',
        grey: '#777777',
        lightgrey: '#aeaeae',
        orange: '#ee5c35'
      },
      customselectors: {
        // When you are unable to add the icon class in HTML and need to use an
        // existing class, add it here!
        // "icon-name": [".selector"]
        "checked": [".form__item--type-checkbox .form-checkbox:checked + label::after"],
        "quote": ["blockquote"],
        "arrow-single": [
          ".pager__item--next a::after",
          ".pager__item--previous a::after",
          "details summary::after",
          ".menu--main .menu__link::after",
          "a.paragraph--type--statistic-item .statistic__text::after",
          ".field--name-field-gallery-items .slick-arrow",
          "#cboxPrevious",
          "#cboxNext"
        ],
        "arrow-single-white": [
          ".pager__item--next a:hover::after",
          ".pager__item--next a:focus::after",
          ".pager__item--previous a:hover::after",
          ".pager__item--previous a:focus::after",
          ".form__item--type-uniform-select",
          ".flexible-block [class*='field--name-field-flexible-block-title']::after",
          ".author .toggle--bio"
        ],
        "arrow-single-orange": [
          ".flexible-blocks-title::after"
        ],
        "arrow-double": [
          ".pager__item--first a::after",
          ".pager__item--last a::after"
        ],
        "arrow-double-white": [
          ".pager__item--first a:hover::after",
          ".pager__item--first a:focus::after",
          ".pager__item--last a:hover::after",
          ".pager__item--last a:focus::after"
        ],
        "status": [".messages--status::before"],
        "warning": [".messages--warning::before"],
        "error": [".messages--error::before"],
        "link": [".paragraph--type--attached-files .file a"],
        "search": [
          ".block--views-exposed-filter-blocksearch-global .form-submit",
          ".hero-search__submit:hover",
          ".hero-search__submit:focus",
          ".search-toggle:not(.is--search-active) .search-toggle__icon:hover",
          ".search-toggle:not(.is--search-active) .search-toggle__icon:focus"
        ],
        "search-white": [
          ".hero-search__submit",
          ".search-toggle:not(.is--search-active)"
        ],
        "phone-grey": [".field--name-field-trust-phone-number"],
        "email-grey": [".field--name-field-trust-email"],
        "kbyg-entry-fee-grey": [".field--name-field-reserve-entry-fee::before"],
        "kbyg-parking-grey": [
          ".field--name-field-reserve-parking-info::before",
          ".field--name-field-event-parking-info::before"
        ],
        "kbyg-bicycle-parking-grey": [
          ".field--name-field-reserve-bicycle-parking::before",
          ".field--name-field-event-bicycle-parking::before"
        ],
        "kbyg-grazing-grey": [".field--name-field-reserve-grazing-animals::before"],
        "kbyg-footprints-grey": [".field--name-field-reserve-walking-trails::before"],
        "kbyg-access-grey": [".field--name-field-reserve-access::before"],
        "kbyg-info-grey": [
          ".reserve__facilities::before",
          ".event__facilities::before"
        ],
        "kbyg-dog-on-a-lead-grey": [
          ".field--name-field-reserve-dogs.dogs--allowed::before",
          ".field--name-field-event-dogs.dogs--allowed::before"
        ],
        "kbyg-no-dogs-grey": [
          ".field--name-field-reserve-dogs.dogs--not-allowed::before",
          ".field--name-field-event-dogs.dogs--not-allowed::before"
        ],
        "kbyg-mobility-grey": [".field--name-field-event-mobility::before"],
        "kbyg-hearing-loop-grey": [".field--name-field-event-hearing-loop::before"],
        "kbyg-wheelchair-grey": [".field--name-field-event-wheelchair::before"],
        "kbyg-what-to-bring-grey": [".field--name-field-event-what-to-bring::before"],
        "social-twitter-grey": [".field--name-field-reserve-twitter a"],
        "social-facebook-grey": [".field--name-field-reserve-facebook a"],
        "social-flickr-grey": [".field--name-field-reserve-flickr a"],
        "social-instagram-lightgrey": [".field--name-field-author-instagram a"],
        "social-linkedin-lightgrey": [".field--name-field-author-linkedin a"],
        "social-twitter-lightgrey": [".field--name-field-author-twitter a"],
        "social-facebook-lightgrey": [".field--name-field-author-facebook a"],
        "social-instagram-white": [
          ".field--name-field-author-instagram a:hover",
          ".field--name-field-author-instagram a:focus"
        ],
        "social-linkedin-white": [
          ".field--name-field-author-linkedin a:hover",
          ".field--name-field-author-linkedin a:focus"
        ],
        "social-twitter-white": [
          ".field--name-field-author-twitter a:hover",
          ".field--name-field-author-twitter a:focus"
        ],
        "social-facebook-white": [
          ".field--name-field-author-facebook a:hover",
          ".field--name-field-author-facebook a:focus"
        ],
        "social-twitter": [
          ".field--name-field-reserve-twitter a:hover",
          ".field--name-field-reserve-twitter a:focus",
        ],
        "social-facebook": [
          ".field--name-field-reserve-facebook a:hover",
          ".field--name-field-reserve-facebook a:focus",
        ],
        "social-flickr": [
          ".field--name-field-reserve-flickr a:hover",
          ".field--name-field-reserve-flickr a:focus"
        ],
        "home": [".breadcrumb li:first-child a"],
        "home-grey": [
          ".breadcrumb li:first-child a:hover",
          ".breadcrumb li:first-child a:focus"
        ]
      }
    }
  }
};
