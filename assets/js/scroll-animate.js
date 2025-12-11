/* scroll-animate.js
   Observa elementos con [data-animate] y les añade la clase in-view cuando entran en el viewport.
   También aplica un ligero parallax a .hero
*/
(function(){
  'use strict';

  // Simple IntersectionObserver to add .in-view
  var observer = null;
  function initObserver(){
    if(!('IntersectionObserver' in window)){
      // fallback: reveal all
      Array.prototype.slice.call(document.querySelectorAll('[data-animate]')).forEach(function(el){
        el.classList.add('in-view');
      });
      return;
    }

    observer = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          var el = entry.target;
          var delay = el.getAttribute('data-delay') || '0';
          // apply inline var for staggering (on the observed element so children inherit)
          el.style.setProperty('--delay', (parseInt(delay)||0) + 'ms');
          // If the animated element is a child with class .animate, add in-view to it
          var animatedChild = el.querySelector && el.querySelector('.animate');
          if(animatedChild){
            animatedChild.classList.add('in-view');
          } else {
            el.classList.add('in-view');
          }
          observer.unobserve(el);
        }
      });
    },{threshold:0.12});

    Array.prototype.slice.call(document.querySelectorAll('[data-animate]')).forEach(function(el){
      observer.observe(el);
    });
  }

  // Parallax hero (very light)
  function initHero(){
    var hero = document.querySelector('.hero');
    if(!hero) return;
    var inner = hero.querySelector('.hero-inner');
    if(!inner) return;

    function onScroll(){
      var rect = hero.getBoundingClientRect();
      var h = window.innerHeight || document.documentElement.clientHeight;
      // only apply when hero is visible
      if(rect.bottom > 0 && rect.top < h){
        var pct = Math.min(1, Math.max(0, (h - rect.top) / (h + rect.height)));
        // translate up slightly
        inner.style.transform = 'translateY(' + Math.round((pct-0.5) * -18) + 'px)';
      }
    }
    onScroll();
    window.addEventListener('scroll', onScroll, {passive:true});
    window.addEventListener('resize', onScroll);
  }

  document.addEventListener('DOMContentLoaded', function(){
    initObserver();
    initHero();
    initReturnLinks();
    restoreScrollFromHash();
  });

  // Interceptar clicks en los enlaces con la clase .view-link para añadir el parámetro return (ruta + scroll)
  function initReturnLinks(){
    document.body.addEventListener('click', function(e){
      var a = e.target.closest && e.target.closest('a.view-link');
      if(!a) return;
      try {
        var href = a.getAttribute('href');
        if(!href) return;
        // Construir return como current path + search + #pos{y}
        var path = window.location.pathname + window.location.search + '#pos' + Math.max(0, Math.round(window.scrollY || window.pageYOffset || 0));
        var sep = href.indexOf('?') === -1 ? '?' : '&';
        var newUrl = href + sep + 'return=' + encodeURIComponent(path);
        e.preventDefault();
        window.location.href = newUrl;
      } catch (err) {
        // allow default on error
      }
    }, false);
  }

  // Si la URL tiene hash #posNNN, desplazar la página a esa posición de scroll
  function restoreScrollFromHash(){
    if(window.location.hash && window.location.hash.indexOf('#pos') === 0){
      var num = parseInt(window.location.hash.replace('#pos',''), 10);
      if(!isNaN(num)){
        // esperar a que el contenido se cargue/animaciones se inicialicen
        setTimeout(function(){ window.scrollTo(0, num); }, 120);
      }
    }
  }

})();
