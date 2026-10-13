<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;

class SearchModal extends Component
{
    public $search = '';
    public $showResults = false;
    public $recentStudents = [];
    public $filter = 'active'; // active, deleted, all

    protected $queryString = ['search', 'filter'];

    public function mount()
    {
        $this->loadRecentStudents();

        // If there's a search query in URL, show results
        if (!empty($this->search)) {
            $this->showResults = strlen($this->search) >= 2;
        }
    }

    public function updatedSearch()
    {
        $this->showResults = strlen($this->search) >= 2;
    }

    public function setFilter($value)
    {
        $this->filter = $value;
    }

    protected function loadRecentStudents()
    {
        $studentIds = Session::get('recent_student_ids', []);
        if (!empty($studentIds)) {
            $this->recentStudents = Student::whereIn('id', $studentIds)
                ->with(['groups' => function ($query) {
                    $query->withTrashed();
                }])
                ->orderByRaw('ARRAY_POSITION(ARRAY[' . implode(',', $studentIds) . ']::int[], id::int)')
                ->get();
        }
    }

    public function saveRecentStudent($studentId)
    {
        $recentIds = Session::get('recent_student_ids', []);

        // Remove if exists and add to beginning
        $recentIds = array_values(array_filter($recentIds, fn($id) => $id != $studentId));
        array_unshift($recentIds, $studentId);

        // Keep only last 5 students
        $recentIds = array_slice($recentIds, 0, 5);

        Session::put('recent_student_ids', $recentIds);
        $this->loadRecentStudents();
    }

    public function redirectToStudent($studentId)
    {
        $this->saveRecentStudent($studentId);
        return redirect()->route('admin.students.show', $studentId);
    }

    public function removeRecentStudent($studentId)
    {
        $recentIds = Session::get('recent_student_ids', []);
        $recentIds = array_values(array_filter($recentIds, fn($id) => $id != $studentId));
        Session::put('recent_student_ids', $recentIds);
        $this->loadRecentStudents();
    }

    public function clearRecentStudents()
    {
        Session::forget('recent_student_ids');
        $this->recentStudents = [];
    }

    public function render()
    {
        $students = collect();

        if ($this->showResults) {
            $query = Student::query();

            // Apply filter
            if ($this->filter === 'all') {
                $query->withTrashed();
            } elseif ($this->filter === 'deleted') {
                $query->onlyTrashed();
            } elseif ($this->filter === 'active') {
                $query->whereNull('deleted_at');
            }

            $students = $query->where(function ($query) {
                // Split search term into parts
                $terms = array_filter(explode(' ', trim($this->search)));

                if (count($terms) >= 2) {
                    // Search for "name surname" format
                    $query->where(function ($q) use ($terms) {
                        $q->where(function ($inner) use ($terms) {
                            $inner->where('name', 'ilike', '%' . $terms[0] . '%')
                                ->where('surname', 'ilike', '%' . $terms[1] . '%');
                        })->orWhere(function ($inner) use ($terms) {
                            // Search for "surname name" format
                            $inner->where('surname', 'ilike', '%' . $terms[0] . '%')
                                ->where('name', 'ilike', '%' . $terms[1] . '%');
                        });
                    });
                } else {
                    // Single word search
                    $query->where('name', 'ilike', '%' . $this->search . '%')
                        ->orWhere('surname', 'ilike', '%' . $this->search . '%')
                        ->orWhere('phone', 'ilike', '%' . $this->search . '%')
                        ->orWhere('parent_phone', 'ilike', '%' . $this->search . '%');
                }
            })
                ->with(['groups' => function ($query) {
                    $query->withTrashed();
                }])
                ->orderBy('name')
                ->limit(10)
                ->get();
        }

        return view('livewire.search-modal', [
            'students' => $students
        ]);
    }
}
