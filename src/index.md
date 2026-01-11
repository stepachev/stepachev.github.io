---
layout: layouts/base.html
title: "Главная"
---

<div class="projects-scroll">
  <div class="projects-track">
    {%- for project in collections.projects | sort(attribute="data.order") -%}
      <a href="{{ project.url }}" class="project-item">
        <img src="{{ project.data.image }}" alt="{{ project.data.title }}">
        <div class="project-title">{{ project.data.title }}</div>
      </a>
    {%- endfor -%}
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.projects-track');
    if (!track) return;

    // Дублируем контент для бесшовного скролла (делаем это несколько раз, чтобы точно хватило ширины)
    // Важно: сначала клонируем все элементы
    const originalContent = track.innerHTML; 
    track.innerHTML += originalContent + originalContent; // Тройное дублирование для надежности

    let position = 0;
    let isDragging = false;
    let startX = 0;
    let oldTranslate = 0;
    let requestID;
    
    // Скорость автоматической прокрутки (пикселей за кадр)
    const baseSpeed = 0.5;
    let speed = baseSpeed;

    // Ширина одного набора элементов. 
    // Поскольку мы утроили контент, полная ширина = 3 * singleWidth.
    // Нам нужно сбрасывать позицию, когда прокрутили 1/3.
    // Вычислим ширину после рендеринга.
    let singleSetWidth = 0;

    function calculateWidth() {
       // Примерно 1/3 от общей ширины трека
       singleSetWidth = track.scrollWidth / 3;
    }
    
    // Ждем загрузки картинок для точного расчета ширины
    window.addEventListener('load', calculateWidth);
    window.addEventListener('resize', calculateWidth);
    // Вызовем сразу на всякий случай
    calculateWidth();

    function animate() {
      if (!isDragging) {
        position -= speed;
      }

      // Бесшовный сброс (работает и при драге, и при скролле)
      if (position <= -singleSetWidth) {
        position += singleSetWidth;
        oldTranslate += singleSetWidth;
      }
      if (position > 0) {
        position -= singleSetWidth;
        oldTranslate -= singleSetWidth;
      }

      track.style.transform = `translateX(${position}px)`;
      requestID = requestAnimationFrame(animate);
    }

    // Запускаем анимацию
    animate();

    // --- Navigation (Mouse, Touch, Wheel) ---

    // Trackpad / Wheel support
    track.addEventListener('wheel', (e) => {
      // Только если скролл в основном горизонтальный
      if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
        e.preventDefault();
        position -= e.deltaX;
      }
    }, { passive: false });

    // Пауза при наведении
    track.addEventListener('mouseenter', () => {
      if (!isDragging) speed = 0;
    });
    
    track.addEventListener('mouseleave', () => {
      if (!isDragging) speed = baseSpeed;
    });

    track.addEventListener('mousedown', startDrag);
    track.addEventListener('touchstart', startDrag, {passive: false});

    track.addEventListener('mousemove', moveDrag);
    track.addEventListener('touchmove', moveDrag, {passive: false});

    track.addEventListener('mouseup', endDrag);
    track.addEventListener('mouseleave', endDrag);
    track.addEventListener('touchend', endDrag);

    let startTouchY = 0;

    function startDrag(e) {
      isDragging = true;
      startX = getPositionX(e);
      if (e.type === 'touchstart') {
        startTouchY = e.touches[0].clientY;
      }
      oldTranslate = position;
      track.style.cursor = 'grabbing';
      if (e.type === 'mousedown') e.preventDefault(); 
    }

    function moveDrag(e) {
      if (!isDragging) return;
      
      const currentX = getPositionX(e);
      const diffX = currentX - startX;
      
      if (e.type === 'touchmove') {
        const currentY = e.touches[0].clientY;
        const diffY = Math.abs(currentY - startTouchY);
        
        // Если движение больше горизонтальное, чем вертикальное — блокируем скролл страницы
        if (Math.abs(diffX) > diffY) {
          e.preventDefault();
        }
      }

      position = oldTranslate + diffX;
    }

    function endDrag() {
      if(!isDragging) return;
      isDragging = false;
      track.style.cursor = 'grab';
    }

    function getPositionX(e) {
      return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
    }
    
    // Предотвращаем клик по ссылке, если был драг
    track.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', (e) => {
        if (Math.abs(position - oldTranslate) > 10) { 
          e.preventDefault();
        }
      });
    });
  });
</script>

<h2 style="margin-top: 80px; margin-bottom: 24px;">Последние записи</h2>

<ul class="post-list">
{% for post in collections.posts | reverse %}
  <li>
    <a href="{{ post.url }}">{{ post.data.title }}</a>
    <span class="post-date">{{ post.date | dateReadable }}</span>
  </li>
{% endfor %}
</ul>
