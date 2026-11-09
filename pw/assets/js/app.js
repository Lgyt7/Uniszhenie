// Клиентская валидация форм (сервер всё равно проверяет повторно)
document.querySelectorAll('form[data-validate]').forEach(form => {
  form.addEventListener('submit', e => {
    let ok = true;
    form.querySelectorAll('.field-error').forEach(n => n.remove());
    form.querySelectorAll('input,textarea,select').forEach(f => {
      f.classList.remove('invalid');
      if (f.type === 'hidden' || f.validity.valid) return;
      ok = false;
      f.classList.add('invalid');
      const msg = document.createElement('span');
      msg.className = 'field-error';
      msg.textContent = f.validity.valueMissing ? 'Заполните это поле'
        : f.validity.typeMismatch ? 'Проверьте формат'
        : f.validity.tooShort ? `Минимум ${f.minLength} символов` : 'Некорректное значение';
      f.insertAdjacentElement('afterend', msg);
    });
    const p1 = form.querySelector('[name=password]'), p2 = form.querySelector('[name=password2]');
    if (p1 && p2 && p1.value !== p2.value) {
      ok = false; p2.classList.add('invalid');
      const msg = document.createElement('span');
      msg.className = 'field-error'; msg.textContent = 'Пароли не совпадают';
      p2.insertAdjacentElement('afterend', msg);
    }
    if (!ok) { e.preventDefault(); form.querySelector('.invalid')?.focus(); }
  });
});

// Ползунок раскаяния и счётчик символов
const range = document.getElementById('remorse'), out = document.getElementById('remorse-out');
if (range && out) range.addEventListener('input', () => out.textContent = range.value);
const ta = document.querySelector('textarea[name=message]'), cnt = document.getElementById('msg-count');
if (ta && cnt) ta.addEventListener('input', () => cnt.textContent = ta.value.length);
