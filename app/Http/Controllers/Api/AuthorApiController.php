<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Author;
use App\Models\Book;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AuthorApiController extends Controller
{
    public function index()
    {
        $authors = Author::with('books')->orderBy('created_at','desc')->get();
        return response()->json(['success' => true, 'data' => $authors]);
    }

    public function store(Request $request)
    {
        $payload = $request->all();

        $validator = Validator::make($payload, [
            'name' => 'required|string|max:255',
             'email' => 'required|email|unique:authors,email',
            'books' => 'required|array|min:1',
             'books.*.name' => 'required|string|max:255',
            'books.*.price' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false, 'errors'=>$validator->errors()], 422);
        }

        $author = Author::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        foreach ($payload['books'] as $b) {
            $author->books()->create([
                'name' => $b['name'],
                'price' => $b['price'],
            ]);
        }

        return response()->json(['success' => true, 'data' => $author->load('books')], 201);
    }

    public function show($id)
    {
        $author = Author::with('books')->find($id);
        if (!$author) {
            return response()->json(['success'=>false, 'message'=>'Author not found'], 404);
        }
        return response()->json(['success'=>true, 'data'=>$author]);
    }

    public function update(Request $request, $id)
    {
        $author = Author::with('books')->find($id);
        if (!$author) {
            return response()->json(['success'=>false, 'message'=>'Author not found'], 404);
        }

        $payload = $request->all();

        $validator = Validator::make($payload, [
            'name' => 'required|string|max:255',
             'email' => ['required','email', Rule::unique('authors','email')->ignore($author->id)],
            'books' => 'required|array|min:1',
             'books.*.id' => 'nullable|integer|exists:books,id',
            'books.*.name' => 'required|string|max:255',
            'books.*.price' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false, 'errors'=>$validator->errors()], 422);
        }

        $author->update([
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        //   update, create new, delete removed
            $incomingIds = [];
            foreach ($payload['books'] as $b) {
                if (isset($b['id'])) {
                    $book = $author->books()->where('id', $b['id'])->first();
                    if ($book) {
                        $book->update(['name'=>$b['name'],'price'=>$b['price']]);
                        $incomingIds[] = $book->id;
                    }
                } else {
                    $new = $author->books()->create(['name'=>$b['name'],'price'=>$b['price']]);
                    $incomingIds[] = $new->id;
                }
            }

       
        $author->books()->whereNotIn('id', $incomingIds)->delete();

        return response()->json(['success'=>true, 'data'=>$author->load('books')]);
    }

    public function destroy($id)
    {
        $author = Author::find($id);
        if (!$author) {
             return response()->json(['success'=>false, 'message'=>'Author not found'], 404);
        }
        $author->books()->delete();
        $author->delete();

         return response()->json(['success'=>true, 'message'=>'Author and related books deleted']);
    }
}