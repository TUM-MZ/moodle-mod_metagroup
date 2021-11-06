import debounce from 'lodash/debounce'

let lang = {}

export const init = function (_lang) {
  lang = _lang
  document
    .querySelectorAll('id_error_field_0, id_error_field_1')
    .forEach(e => e.addEventListener('change', debounce(loadCourses, 250)))
  document
    .querySelector('#id_search')
    .addEventListener('keyup', debounce(loadCourses, 250))
  document
    .querySelector('#id_link')
    .addEventListener('change', loadGroups)
}

async function loadCourses (keyupEvent) {
  if (keyupEvent.code === 13) {
    keyupEvent.preventDefault()
    return
  }
  const filterExpr = keyupEvent.target.value.trim()
  if (filterExpr.length < 3) {
    return
  }
  const options = await window.fetch('/enrol/metagroup/courses.json.php', {
    method: 'POST',
    body: new window.FormData(document.querySelector('form'))
  }).then(r => r.json())
  const link = document.querySelector('#id_link')
  if (options.length === 0) {
    link.setAttribute('disabled', 'disabled')
    link.innerHTML = `<option>${lang.no_courses}</option>`;
    window.setTimeout(() => {
      link.value = '';
      link.dispatchEvent(new Event("change"));
    });
    return
  }
  link.innerHTML = options
    .map(o => `<option value="${o.id}">${o.name} [${o.idnumber}]</option>`)
    .join('')
  link.removeAttribute('disabled')
  window.setTimeout(() => {
    link.value = options[0].id
    link.dispatchEvent(new Event('change'))
  })
}

async function loadGroups (changeEvent) {
  const courseid = changeEvent.target.value
  const group = document.querySelector('#id_groups');
  if (courseid === '') {
    group.setAttribute("disabled", "disabled");
    group.innerHTML = '';
    return;
  }
  const groups = await window
    .fetch('/enrol/metagroup/groups.json.php', {
      method: 'POST',
      body: new window.URLSearchParams({ courseid }),
    })
    .then((r) => r.json())
  if (groups.length === 0) {
    group.setAttribute('disabled', 'disabled')
    group.innerHTML = `<option>${lang.no_groups}</option>`;
    return
  }
  group.innerHTML = groups
    .map((g) => `<option value="${g.id}">${g.name}</option>`)
    .join('');
  group.removeAttribute('disabled')
}
