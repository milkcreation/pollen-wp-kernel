'use strict';

import jQuery from 'jquery';
import '@pollen-solutions/partial/resources/assets/src/js/media-library';

jQuery(function ($) {
  $.widget('tify.tifyMediaImage', {
    widgetEventPrefix: 'media-image:',
    options: {
      classes: {}
    },
    control: {
      input: 'media-image.input',
      opener: 'media-image.open',
      preview: 'media-image.preview',
      remover: 'media-image.remove'
    },
    // Instanciation de l'élément.
    _create: function () {
      this.instance = this;
      this.el = this.element;

      this._initOptions();
      this._initControls();
      this._initEvents();
    },
    // INTIALISATIONS
    // -----------------------------------------------------------------------------------------------------------------
    // Initialisation des attributs de configuration.
    _initOptions: function () {
      $.extend(
          true,
          this.options,
          this.el.data('options') && $.parseJSON(decodeURIComponent(this.el.data('options'))) || {}
      );
    },
    // Initialisation de la médiathèque.
    _initControls: function () {
      let self = this;

      if (this.library === undefined) {
        this.library = $(this.el).tifyMediaLibrary({multiple: false, library: {type: 'image'}});
        $(this.el).on('media-library:select', function (e, items) {
          let item = items[0] || {};

          $('[data-control="' + self.control.preview + '"]', self.el).css('background-image', 'url(' + item.url + '').show();
          $('[data-control="' + self.control.input + '"]', self.el).val(item.id);
          self.el.attr('aria-selected', 'true');
        });
      }
    },
    // Initialisation des événements.
    _initEvents: function () {
      this._on(this.el, {'click [data-control="media-image.open"]': this._onClickOpen});
      this._on(this.el, {'click [data-control="media-image.remove"]': this._onClickRemove});
    },
    // EVENEMENTS
    // -----------------------------------------------------------------------------------------------------------------
    // Au clic sur le bouton d'ouverture de la médiathèque.
    _onClickOpen: function (e) {
      e.preventDefault();

      this.open();

      this._trigger('open');
    },
    // Au clic sur le bouton de suppression.
    _onClickRemove: function (e) {
      e.preventDefault();

      this.remove();

      this._trigger('remove');
    },
    // ACCESSEURS.
    // -----------------------------------------------------------------------------------------------------------------
    // Ouverture de la médiathèque.
    open: function () {
      this.library.tifyMediaLibrary('open');
    },
    // Suppression de l'image.
    remove: function () {
      let self = this;

      self.el.attr('aria-selected', 'false');

      $('[data-control="' + this.control.preview + '"]', this.el).fadeOut(function () {
        let def = $('[data-control="' + self.control.preview + '"]', self.el).data('default');

        $(this).css('background-image', def ? 'url('+def+')' : '').fadeIn();
        $('[data-control="' + self.control.input + '"]', self.el).val(self.option('value_none') || '');
      });
    }
  });

  $(document).ready(function () {
    $(document).on('mouseenter', '[data-control="media-image"]', function() {
      $(this).tifyMediaImage();
    });
  });
});