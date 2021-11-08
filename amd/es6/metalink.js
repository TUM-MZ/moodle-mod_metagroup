import { filter } from 'lodash'
import debounce from 'lodash/debounce'

let lang = {}

export const init = function (_lang) {
  lang = _lang
  document
    .querySelectorAll('id_error_field_0, id_error_field_1')
    .forEach(e => e.addEventListener('change', debounce(handleLoadCourses, 250)))

  const search = document.querySelector('#id_search')
  search.addEventListener('keyup', debounce(handleLoadCourses, 250))

  const link = document.querySelector('#id_link')
  link.outerHTML = `<select class="custom-select" name=${link.name} id="id_link" disabled></select>`
  link.addEventListener('change', handleLoadGroups)

  const group = document.querySelector('#id_group')
  group.outerHTML = `<select class="custom-select" name="${group.name}" id="id_group" disabled></select>`

  const filterExpr = search.value.trim()
  if (filterExpr.length >= 3) {
    loadCourses(filterExpr)
  }
}

async function handleLoadCourses (keyupEvent) {
  if (keyupEvent.code === 13) {
    keyupEvent.preventDefault()
    return
  }
  const filterExpr = keyupEvent.target.value.trim()
  if (filterExpr.length >= 3) {
    loadCourses(filterExpr)
  }
}

async function loadCourses (filterExpr) {
  const options = await window.fetch('/enrol/metagroup/courses.json.php', {
    method: 'POST',
    body: new window.FormData(document.querySelector('form'))
  }).then(r => r.json())
  const link = document.querySelector('#id_link')
  if (options.length === 0) {
    link.setAttribute('disabled', 'disabled')
    link.setAttribute('size', 1)
    link.innerHTML = `<option>${lang.no_courses}</option>`
    window.setTimeout(() => {
      link.value = ''
      loadGroups(false)
    })
    return
  }
  link.innerHTML = options
    .map(o => `<option value="${o.id}">${o.name} [${o.idnumber}]</option>`)
    .join('')
  link.removeAttribute('disabled')
  link.setAttribute('size', Math.min(10, options.length))
  window.setTimeout(() => {
    link.value = options[0].id
    loadGroups(options[0].id)
  })
}

async function handleLoadGroups (changeEvent) {
  loadGroups(changeEvent.target.value)
}

async function loadGroups (courseid) {
  const group = document.querySelector('#id_group')
  if (courseid === '') {
    group.setAttribute('disabled', 'disabled')
    group.setAttribute('size', 1)
    group.innerHTML = ''
    return
  }
  const groups = await window
    .fetch('/enrol/metagroup/groups.json.php', {
      method: 'POST',
      body: new window.URLSearchParams({ courseid })
    })
    .then((r) => r.json())
  if (groups.length === 0) {
    group.setAttribute('disabled', 'disabled')
    group.setAttribute('size', 1)
    group.innerHTML = `<option>${lang.no_groups}</option>`
    return
  }
  group.innerHTML = groups
    .map((g) => `<option value="${g.id}">${g.name}</option>`)
    .join('')
  group.removeAttribute('disabled')
  group.setAttribute('size', Math.min(10, groups.length))
}
