document.addEventListener('DOMContentLoaded',()=>{
  const toggle=document.querySelector('.menu-toggle'),nav=document.querySelector('.site-header nav');
  if(toggle&&nav)toggle.addEventListener('click',()=>{nav.classList.toggle('open');toggle.setAttribute('aria-expanded',nav.classList.contains('open')?'true':'false');});

  // Homepage slider: previous/next controls, dots and automatic rotation.
  const slides=[...document.querySelectorAll('.hero-slide')];
  const dots=[...document.querySelectorAll('.hero-dots button')];
  let current=Math.max(0,slides.findIndex(s=>s.classList.contains('active')));
  let sliderTimer;
  function show(n){
    if(!slides.length)return;
    current=(n+slides.length)%slides.length;
    slides.forEach((s,i)=>{s.classList.toggle('active',i===current);s.setAttribute('aria-hidden',i===current?'false':'true');});
    dots.forEach((d,i)=>{d.classList.toggle('active',i===current);d.setAttribute('aria-current',i===current?'true':'false');});
  }
  function restartSlider(){if(sliderTimer)clearInterval(sliderTimer);if(slides.length>1)sliderTimer=setInterval(()=>show(current+1),5500);}
  document.querySelector('.hero-next')?.addEventListener('click',()=>{show(current+1);restartSlider();});
  document.querySelector('.hero-prev')?.addEventListener('click',()=>{show(current-1);restartSlider();});
  dots.forEach((d,i)=>d.addEventListener('click',()=>{show(i);restartSlider();}));
  show(current);restartSlider();

  // The two floating page controls have distinct destinations.
  document.querySelectorAll('[data-scroll]').forEach(button=>button.addEventListener('click',()=>{
    const destination=button.dataset.scroll==='bottom'?document.documentElement.scrollHeight:0;
    window.scrollTo({top:destination,behavior:'smooth'});
  }));

  document.querySelectorAll('.filters button').forEach(b=>b.addEventListener('click',()=>{document.querySelectorAll('.filters button').forEach(x=>x.classList.remove('selected'));b.classList.add('selected');document.querySelectorAll('.gallery-item').forEach(item=>item.hidden=!(b.dataset.filter==='all'||item.dataset.category===b.dataset.filter));}));
  const lb=document.querySelector('#lightbox');
  if(lb){document.querySelectorAll('.gallery-item').forEach(a=>a.addEventListener('click',e=>{e.preventDefault();lb.querySelector('img').src=a.href;lb.classList.add('open')}));lb.querySelector('button')?.addEventListener('click',()=>lb.classList.remove('open'));lb.addEventListener('click',e=>{if(e.target===lb)lb.classList.remove('open')});}
});
