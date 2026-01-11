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



    // --- Navigation (Mouse, Touch, Wheel) ---

    let velocity = 0;
    let lastX = 0;
    let lastTime = 0;
    let dragDirection = null; // 'horizontal' or 'vertical'

    track.addEventListener('wheel', (e) => {
      if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
        e.preventDefault();
        position -= e.deltaX;
        velocity = -e.deltaX * 0.5; // Немного инерции для колеса
      }
    }, { passive: false });

    track.addEventListener('mouseenter', () => { if (!isDragging) speed = 0; });
    track.addEventListener('mouseleave', () => { if (!isDragging) speed = baseSpeed; });

    track.addEventListener('mousedown', startDrag);
    track.addEventListener('touchstart', startDrag, {passive: false});
    window.addEventListener('mousemove', moveDrag);
    window.addEventListener('touchmove', moveDrag, {passive: false});
    window.addEventListener('mouseup', endDrag);
    window.addEventListener('touchend', endDrag);

    function startDrag(e) {
      isDragging = true;
      dragDirection = null;
      startX = getPositionX(e);
      startY = e.type === 'touchstart' ? e.touches[0].clientY : 0;
      lastX = startX;
      lastTime = Date.now();
      oldTranslate = position;
      velocity = 0;
      track.style.cursor = 'grabbing';
      if (e.type === 'mousedown') e.preventDefault();
    }

    function moveDrag(e) {
      if (!isDragging) return;
      
      const currentX = getPositionX(e);
      const currentY = e.type === 'touchmove' ? e.touches[0].clientY : 0;
      
      if (e.type === 'touchmove' && !dragDirection) {
        const diffX = Math.abs(currentX - startX);
        const diffY = Math.abs(currentY - startY);
        if (diffX > 5 || diffY > 5) {
          dragDirection = diffX > diffY ? 'horizontal' : 'vertical';
        }
      }

      if (dragDirection === 'vertical') {
        isDragging = false;
        return;
      }

      if (e.type === 'touchmove' && dragDirection === 'horizontal') {
        e.preventDefault();
      }

      const now = Date.now();
      const dt = now - lastTime;
      if (dt > 0) {
        velocity = (currentX - lastX) / dt * 16; // Пикселей за кадр (60fps)
        lastX = currentX;
        lastTime = now;
      }

      const diff = currentX - startX;
      position = oldTranslate + diff;
    }

    function endDrag() {
      if (!isDragging) return;
      isDragging = false;
      track.style.cursor = 'grab';
    }

    function getPositionX(e) {
      return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
    }

    // Модифицируем функцию animate для учета инерции
    function updateAnimation() {
      if (!isDragging) {
        // Применяем инерцию
        position += velocity;
        velocity *= 0.95; // Коэффициент затухания

        // Возвращаемся к базовой скорости, когда инерция затухла
        if (Math.abs(velocity) < 0.1) {
          velocity = 0;
          position -= speed;
        }
      }

      // Бесшовный сброс
      if (position <= -singleSetWidth) {
        position += singleSetWidth;
        oldTranslate += singleSetWidth;
      }
      if (position > 0) {
        position -= singleSetWidth;
        oldTranslate -= singleSetWidth;
      }

      track.style.transform = `translateX(${position}px)`;
      requestAnimationFrame(updateAnimation);
    }

    // Заменяем старый вызов animate
    // animate(); // Удаляем
    requestAnimationFrame(updateAnimation);

    // Предотвращаем клик по ссылке, если был драг
    track.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', (e) => {
        if (Math.abs(position - oldTranslate) > 10 || Math.abs(velocity) > 1) { 
          e.preventDefault();
        }
      });
    });
  });
</script>


<ul class="post-list">
{% for post in collections.posts | reverse %}
  <li>
    <a href="{{ post.url }}">{{ post.data.title }}</a>
    <span class="post-date">{{ post.date | dateReadable }}</span>
  </li>
{% endfor %}
</ul>
