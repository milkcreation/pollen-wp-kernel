'use strict';

class WpAdminBarPos {
  static _staticElements = {}

  constructor(el, options = {}) {
    this.initialized = false
    this.verbose = false
    this.floatings = ['absolute', 'fixed']
    this.style = window.getComputedStyle(el)
    this.cache = {}

    if (!el.id) {
      const ID = this.generateUniqueID();
      el.id = ID

      this.constructor._staticElements[ID] = {
        pos: this.style.position || 'static'
      }
    }

    this.cache = this.constructor._staticElements[el.id] || {}

    if (this.floatings.indexOf(this.cache.pos) === 1) {
      this.cache.top = this.cache.top ?? this.style.top

      if (window.matchMedia('(max-width: 600px)').matches) {
        if (window.scrollY > 46) {
          el.style.position = this.cache.pos
          el.style.top = parseInt(this.cache.top) +'px'
        } else {
          el.style.position = 'absolute'
          el.style.top = parseInt(this.cache.top) + 46 + 'px'
        }
      } else if (window.matchMedia('(max-width: 782px)').matches) {
        el.style.position = this.cache.pos
        el.style.top = parseInt(this.cache.top) + 46 + 'px'
      } else {
        el.style.position = this.cache.pos
        el.style.top = parseInt(this.cache.top) + 32 + 'px'
      }
    } else {
      this.cache.top = this.cache.top ?? parseInt(this.style.marginTop)

      if (window.matchMedia('(max-width: 600px)').matches) {
        el.style.marginTop = parseInt(this.cache.top) + 'px'
      } else if (window.matchMedia('(max-width: 782px)').matches) {
        el.style.marginTop = parseInt(this.cache.top) + 46 + 'px'
      } else {
        el.style.marginTop = parseInt(this.cache.top) + 32 + 'px'
      }
    }
  }

  generateUniqueID = () => {
    return '_' + Math.random().toString(36).substr(2, 9);
  }
}

window.addEventListener('load', e => {
  if (!document.body.classList.contains('admin-bar')) {
    return
  }

  const $elements = document.querySelectorAll('[data-observer="wp-admin-bar-pos"]')

  $elements.forEach($el => new WpAdminBarPos($el))

  window.addEventListener('scroll', function (e) {
    $elements.forEach($el => new WpAdminBarPos($el))
  })

  window.addEventListener('resize', function (e) {
    $elements.forEach($el => new WpAdminBarPos($el))
  })
})

export default WpAdminBarPos