'use strict';

import jQuery from 'jquery';
import 'jquery-ui/ui/core';
import 'jquery-ui/ui/widget';
import 'jquery-ui/ui/widgets/sortable';
import MutationObserver from '@pollen-solutions/support/resources/assets/src/js/mutation-observer';
import '@pollen-solutions/partial/resources/assets/src/js/media-library';

jQuery(function ($) {
  $.widget('tify.tifyMediaFile', {
    widgetEventPrefix: 'media-file:',
    options: {},
    control: {
      addnew: 'media-file.addnew',
      input: 'media-file.input',
      reset: 'media-file.reset',
      wrap: 'media-file.wrap',
    },
    library: undefined,
    // Instanciation de l'élément.
    _create: function () {
      this.instance = this;

      this.el = this.element;

      this._initOptions();
      this._initControls();
      this._initEvents();
    },
    // INITIALISATIONS.
    // -----------------------------------------------------------------------------------------------------------------
    // Initialisation des attributs de configuration.
    _initOptions: function () {
      $.extend(
          true,
          this.options,
          this.el.data('options') && $.parseJSON(decodeURIComponent(this.el.data('options'))) || {}
      );
    },
    // Initialisation des agents de contrôle.
    _initControls: function () {
      this.wrap = $(this.el).closest('[data-control="' + this.control.wrap + '"]');
      if (!this.wrap.length) {
        this.wrap = $(this.el).wrap('<div data-control="' + this.control.wrap + '"/>').parent();
      }
      this.wrap.addClass(this.option('classes.wrap'));

      let $input = $('[data-control="' + this.control.input + '"]', this.wrap);
      if (!$input.length) {
        $input = $('<input type="hidden" data-control="' + this.control.input + '"/>').appendTo(this.wrap);
      }
      $input.addClass(this.option('classes.input'));

      let name = this.el.attr('name') || undefined,
          value = this.el.val() || undefined;
      if (name !== undefined) {
        $input.attr('name', name);
        this.el.removeAttr('name');
      }
      if (value !== undefined) {
        $input.attr('value', value);
        this.wrap.attr('aria-active', 'true');
        this.el.val(this.el.data('value'));
      } else {
        this.wrap.attr('aria-active', 'false');
      }

      let $addnew = $('[data-control="' + this.control.addnew + '"]', this.wrap);
      if (!$addnew.length) {
        $addnew = $('<a href="#" data-control="' + this.control.addnew + '"/>').appendTo(this.wrap).text('+');
      }
      $addnew.addClass(this.option('classes.addnew'));

      let $reset = $('[data-control="' + this.control.reset + '"]', this.wrap);
      if (!$reset.length) {
        $reset = $('<span data-control="' + this.control.reset + '"/>').appendTo(this.wrap);
      }
      $reset.addClass(this.option('classes.reset'));
    },
    // Initialisation des événements.
    _initEvents: function () {
      this._on(this.wrap, {'click [data-control="media-file.addnew"]': this._onAddnew});
      this._on(this.wrap, {'click [data-control="media-file.reset"]': this._onReset});
    },
    // EVENEMENTS.
    // -----------------------------------------------------------------------------------------------------------------
    // Ajout d'un élément.
    _onAddnew: function (e) {
      e.preventDefault();

      let self = this;

      if (this.library === undefined) {
        this.library = $(e.target).tifyMediaLibrary(this.option('library') || {});
        $(e.target).on('media-library:select', function (e, selection) {
          let file = selection[0] || {};

          self.wrap.attr('aria-active', 'true');
          self.el.val(file.title + ' → ' + file.filename);
          $('[data-control="' + self.control.input + '"]', self.wrap).val(file.id);
        });
      }
      this.library.tifyMediaLibrary('open');
    },
    // Réinitialisation de l'élément.
    _onReset: function (e) {
      e.preventDefault();

      this.wrap.attr('aria-active', 'false');
      this.el.val('');
      $('[data-control="' + this.control.input + '"]', this.wrap).val('');
    }
  });

  $(document).ready(function ($) {
    $('[data-control="media-file"]').tifyMediaFile();

    MutationObserver('[data-control="media-file"]', function (target) {
      $(target).tifyMediaFile();
    })
  });
});