(function(){
  var b=document.querySelector('.burger'),n=document.getElementById('nav');
  if(b&&n){b.addEventListener('click',function(){var o=n.classList.toggle('open');b.setAttribute('aria-expanded',o);document.body.style.overflow=o?'hidden':''})}
  document.querySelectorAll('.dd>button').forEach(function(t){t.addEventListener('click',function(){var d=t.parentNode,o=d.classList.toggle('open');t.setAttribute('aria-expanded',o)})});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'){document.querySelectorAll('.dd.open').forEach(function(d){d.classList.remove('open')})}});
  var h=document.querySelector('.hdr'),s=document.getElementById('top');
  if(h&&s&&'IntersectionObserver'in window){new IntersectionObserver(function(e){h.classList.toggle('scrolled',!e[0].isIntersecting)}).observe(s)}
  if('IntersectionObserver'in window){var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target)}})},{rootMargin:'0px 0px -8% 0px'});document.querySelectorAll('.rv').forEach(function(el){io.observe(el)})}else{document.querySelectorAll('.rv').forEach(function(el){el.classList.add('in')})}
  document.querySelectorAll('.ba input').forEach(function(r){var ba=r.closest('.ba');function u(){ba.style.setProperty('--p',r.value+'%')}r.addEventListener('input',u);u()});
  var TEL='06 15 17 59 70',TELH='+33615175970';
  document.querySelectorAll('form.lead').forEach(function(f){
    f.addEventListener('submit',function(e){
      e.preventDefault();var m=f.querySelector('.msg'),btn=f.querySelector('button[type=submit]'),d=new FormData(f);
      if(d.get('_honey'))return;
      var files=f.querySelector('input[type=file]'),tot=0;
      if(files&&files.files){if(files.files.length>5){m.className='msg err';m.textContent='5 photos maximum.';return}for(var i=0;i<files.files.length;i++)tot+=files.files[i].size;if(tot>10*1024*1024){m.className='msg err';m.textContent='Photos trop lourdes (10 Mo maximum au total).';return}}
      m.className='msg';m.textContent='Envoi en cours...';btn.disabled=true;
      fetch(f.action,{method:'POST',body:d,headers:{'Accept':'application/json'}})
        .then(function(r){return r.json().then(function(j){if(!r.ok||!j.ok)throw j;return j})})
        .then(function(){m.className='msg ok';m.textContent='Merci, votre demande est bien envoyée. Nous vous rappelons rapidement.';f.reset();btn.disabled=false;if(window.gtag)gtag('event','generate_lead')})
        .catch(function(){m.className='msg err';m.innerHTML='Envoi impossible pour le moment. Appelez-nous au <a href="tel:'+TELH+'">'+TEL+'</a>.';btn.disabled=false});
    });
  });
})();
