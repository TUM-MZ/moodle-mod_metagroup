import debounce from 'lodash/debounce'

export const init = function () {
  console.log('Hallo')
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

async function loadCourses (ev) {
  if (ev.code === 13) {
    ev.preventDefault()
    return
  }
  const filterExpr = ev.target.value.trim()
  if (filterExpr.length < 3) {
    return
  }
  const options = await window.fetch('/enrol/metagroup/courses.json.php', {
    method: 'POST',
    body: new window.FormData(document.querySelector('form'))
  }).then(r => r.json())
  const link = document.querySelector('#id_link')
  link.innerHTML = options
    .map(o => `<option value="${o.id}">${o.name} [${o.idnumber}]</option>`)
    .join('')
  link.removeAttribute('disabled')
}

function loadGroups (elem) {
  window.fetch('/enrol/metagroup/groups.json.php', {
    body: {

      courseid: document.querySelector(elem).val()
    }
  }).then(groups => {
    if (Object.keys(groups).length > 0) {
      // document.querySelector('#id_courseg').prop( "disabled", false );
      document.querySelector('#id_groups') // initialize select element
        .find('option')
        .remove()
        .end()
        .append('<option value="0">All</option>')
        .val('0')
    } else {
      // document.querySelector('#id_courseg').prop( "disabled", true );
      document.querySelector('#id_groups') // initialize select element
        .find('option')
        .remove()
    }
    Object.keys(groups).map(function (key) {
      document.querySelector('#id_groups')
        .append(document.querySelector('<option></option>')
          .attr('value', groups[key].id)
          .text(groups[key].name))
    })
  })
}
