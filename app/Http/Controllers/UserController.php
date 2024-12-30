<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; 
class UserController extends Controller {

private $filePath = 'app/private/awokwa.json';
    // Fungsi untuk membaca pengguna dari file JSON
    private function readUsersFromFile()
    {
        $path = storage_path($this->filePath);
        if (!File::exists($path)) {
            return [];
        }

        $data = File::get($path);
        return json_decode($data, true);
    }

    // Fungsi untuk menyimpan pengguna ke file JSON
    private function writeUsersToFile($users)
    {
        $path = storage_path($this->filePath);
        try {
            File::put($path, json_encode($users, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            Log::error('Error writing to file: ' . $e->getMessage());
            throw new \Exception('Failed to write users to file.');
        }
    }
    

    // Endpoint untuk login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Membaca data pengguna dari file JSON
        $users = $this->readUsersFromFile();
        $user = collect($users)->firstWhere('username', $request->username);
   
      

    if ($user && Hash::check($request->password, $user['password'])) {
        return response()->json(['name' => $user['name'], 'username' => $user['username']]);
    } else {
        return response()->json([
            'message' => 'Invalid username or password'
        ]);
    }  
    
    
    }
    
        // Mengambil semua pengguna
        public function getUsers()
        {
            $users = $this->readUsersFromFile();
            return response()->json($users);
        }
        
        
    // Endpoint untuk menghapus pengguna
    public function deleteUser(Request $request)
    {
        $request->validate([
            'UUID' => 'required|string',
        ]);

        $users = $this->readUsersFromFile();

        $filteredUsers = array_filter($users, function ($user) use ($request) {
            return $user['UUID'] !== $request->UUID;
        });

        if (count($users) === count($filteredUsers)) {
            return response()->json(['message' => 'User not found'], 404);
        }
        

        $this->writeUsersToFile(array_values($filteredUsers));
        return response()->json(['message' => 'User deleted successfully']);

    }
       
          // Endpoint untuk memperbarui pengguna
    public function updateUser(Request $request)
    {
        try {
            $uuid = $request->input('UUID');
            $users = $this->readUsersFromFile();

            // Cari pengguna berdasarkan UUID
            $userIndex = array_search($uuid, array_column($users, 'UUID'));

            if ($userIndex === false) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Perbarui data pengguna
            $users[$userIndex]['username'] = $request->input('username', $users[$userIndex]['username']);
            $users[$userIndex]['name'] = $request->input('name', $users[$userIndex]['name']);
            $users[$userIndex]['email'] = $request->input('email', $users[$userIndex]['email']);
            $users[$userIndex]['role'] = $request->input('role', $users[$userIndex]['role']);

            $this->writeUsersToFile($users);

            return response()->json(['message' => $users[$userIndex]['username'] .' updated successfully']);
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing request'], 500);
        }
    }
    
        public function generateUUIDForUsers()
        {
            try {
                $users = $this->readUsersFromFile();
    
                // Generate UUID untuk setiap pengguna jika belum ada
                foreach ($users as &$user) {
                    if (!isset($user['UUID']) || empty($user['UUID'])) {
                        $user['UUID'] = (string) \Illuminate\Support\Str::uuid();
                    }
                }
    
                $this->writeUsersToFile($users);
    
                return response()->json(['message' => 'UUID generated for all users', 'users' => $users]);
            } catch (\Exception $e) {
                Log::error('Error generating UUID: ' . $e->getMessage());
                return response()->json(['message' => 'Error processing request'], 500);
            }
        }
    
}