function openEditModal(data) {
    document.getElementById('edit_room_id').value = data.room_id;
    document.getElementById('edit_room_name').value = data.room_name;
    document.getElementById('edit_room_type').value = data.room_type;
    document.getElementById('edit_capacity').value = data.capacity;
    document.getElementById('edit_building_id').value = data.building_id;
    document.getElementById('editModal').classList.add('is-active');
}

function closeModal() {
    document.getElementById('editModal').classList.remove('is-active');
}