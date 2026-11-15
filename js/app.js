const API_URL = 'php/api.php';

const employeeForm = document.getElementById('employeeForm');
const formTitle = document.getElementById('formTitle');
const submitBtn = document.getElementById('submitBtn');
const cancelEditBtn = document.getElementById('cancelEditBtn');
const formMessage = document.getElementById('formMessage');
const tableBody = document.getElementById('employeeTableBody');
const searchInput = document.getElementById('searchInput');
const filterDepartment = document.getElementById('filterDepartment');

let currentEmployees = [];

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text ?? '';
  return div.innerHTML;
}

function showMessage(text, isError = false) {
  formMessage.textContent = text;
  formMessage.className = `message ${isError ? 'error' : 'success'}`;
  setTimeout(() => {
    formMessage.textContent = '';
  }, 4000);
}

function getBadgeClass(status) {
  if (status === 'Remote') return 'badge-remote';
  if (status === 'On Leave') return 'badge-leave';
  return 'badge-active';
}

async function loadEmployees() {
  const params = new URLSearchParams();
  if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
  if (filterDepartment.value) params.set('department', filterDepartment.value);

  try {
    const res = await fetch(`${API_URL}?${params.toString()}`);
    const data = await res.json();

    if (!data.success) {
      tableBody.innerHTML = '<tr><td colspan="8" class="empty-row">Could not load employees.</td></tr>';
      return;
    }

    currentEmployees = data.employees;
    document.getElementById('statTotal').textContent = data.stats.total;
    document.getElementById('statActive').textContent = data.stats.active;
    document.getElementById('statRemote').textContent = data.stats.remote;
    document.getElementById('statLeave').textContent = data.stats.on_leave;

    if (currentEmployees.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="8" class="empty-row">No employees found.</td></tr>';
      return;
    }

    tableBody.innerHTML = currentEmployees
      .map(
        (emp) => `
      <tr>
        <td><strong>${escapeHtml(emp.full_name)}</strong></td>
        <td>${escapeHtml(emp.email)}</td>
        <td>${escapeHtml(emp.department)}</td>
        <td>${escapeHtml(emp.role_title)}</td>
        <td><span class="badge ${getBadgeClass(emp.status)}">${escapeHtml(emp.status)}</span></td>
        <td>${Number(emp.salary).toLocaleString()}</td>
        <td>${escapeHtml(emp.joined_date)}</td>
        <td>
          <button type="button" class="btn btn-edit" onclick="editEmployee(${Number(emp.id)})">Edit</button>
          <button type="button" class="btn btn-delete" onclick="deleteEmployee(${Number(emp.id)})">Delete</button>
        </td>
      </tr>
    `
      )
      .join('');
  } catch (err) {
    tableBody.innerHTML = '<tr><td colspan="8" class="empty-row">Error connecting to server. Make sure PHP and MySQL are running.</td></tr>';
  }
}

employeeForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id = document.getElementById('employeeId').value;

  const payload = {
    id: id ? Number(id) : undefined,
    full_name: document.getElementById('fullName').value.trim(),
    email: document.getElementById('email').value.trim(),
    department: document.getElementById('department').value,
    role_title: document.getElementById('roleTitle').value.trim(),
    status: document.getElementById('status').value,
    salary: Number(document.getElementById('salary').value) || 0,
    joined_date: document.getElementById('joinedDate').value,
  };

  try {
    const res = await fetch(API_URL, {
      method: id ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (data.success) {
      showMessage(data.message);
      resetForm();
      loadEmployees();
    } else {
      showMessage(data.message || 'Operation failed.', true);
    }
  } catch (err) {
    showMessage('Server error while saving employee.', true);
  }
});

window.editEmployee = function (id) {
  const emp = currentEmployees.find((item) => Number(item.id) === Number(id));
  if (!emp) return;

  document.getElementById('employeeId').value = emp.id;
  document.getElementById('fullName').value = emp.full_name;
  document.getElementById('email').value = emp.email;
  document.getElementById('department').value = emp.department;
  document.getElementById('roleTitle').value = emp.role_title;
  document.getElementById('status').value = emp.status;
  document.getElementById('salary').value = emp.salary;
  document.getElementById('joinedDate').value = emp.joined_date;

  formTitle.textContent = 'Edit Employee';
  submitBtn.textContent = 'Update Employee';
  cancelEditBtn.classList.remove('hidden');
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.deleteEmployee = async function (id) {
  if (!confirm('Are you sure you want to delete this employee?')) return;

  try {
    const res = await fetch(`${API_URL}?id=${Number(id)}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      showMessage(data.message);
      loadEmployees();
    } else {
      showMessage(data.message || 'Could not delete employee.', true);
    }
  } catch (err) {
    showMessage('Server error while deleting employee.', true);
  }
};

function resetForm() {
  employeeForm.reset();
  document.getElementById('employeeId').value = '';
  formTitle.textContent = 'Add New Employee';
  submitBtn.textContent = 'Add Employee';
  cancelEditBtn.classList.add('hidden');
}

cancelEditBtn.addEventListener('click', resetForm);
searchInput.addEventListener('input', loadEmployees);
filterDepartment.addEventListener('change', loadEmployees);

document.addEventListener('DOMContentLoaded', loadEmployees);
