<?php

namespace DarwinNatha\Process\Tests\Feature;

use DarwinNatha\Process\Support\ProcessPayload;
use DarwinNatha\Process\Tests\TestCase;
use Illuminate\Http\Request;

class ProcessPayloadTest extends TestCase
{
    public function test_can_create_payload_from_request()
    {
        $request = new Request(['name' => 'John', 'email' => 'john@example.com']);
        $request->setUserResolver(function () {
            return (object) ['id' => 123];
        });
        
        $payload = ProcessPayload::fromRequest($request);
        
        $this->assertEquals('John', $payload->get('name'));
        $this->assertEquals('john@example.com', actual: $payload->get('email'));
        $this->assertEquals('http', $payload->getSource());
        $this->assertEquals(123, $payload->getMetadata('user_id'));
    }

    public function test_can_create_payload_from_job()
    {
        $payload = ProcessPayload::fromJob(['user_id' => 456, 'action' => 'send_email'], 'SendEmailJob');
        
        $this->assertEquals(456, $payload->get('user_id'));
        $this->assertEquals('send_email', $payload->get('action'));
        $this->assertEquals('job', $payload->getSource());
        $this->assertEquals('SendEmailJob', $payload->getMetadata('job_class'));
    }

    public function test_can_create_payload_from_command()
    {
        $payload = ProcessPayload::fromCommand(['--force' => true, 'name' => 'test'], 'my:command');
        
        $this->assertTrue($payload->get('--force'));
        $this->assertEquals('test', $payload->get('name'));
        $this->assertEquals('cli', $payload->getSource());
        $this->assertEquals('my:command', $payload->getMetadata('command'));
    }

    public function test_can_create_payload_from_event()
    {
        $payload = ProcessPayload::fromEvent(['user_id' => 789], 'UserRegistered');
        
        $this->assertEquals(789, $payload->get('user_id'));
        $this->assertEquals('event', $payload->getSource());
        $this->assertEquals('UserRegistered', $payload->getMetadata('event_class'));
    }

    public function test_can_access_data_like_array()
    {
        $payload = ProcessPayload::make(['key' => 'value']);
        
        $this->assertEquals('value', $payload['key']);
        $this->assertTrue(isset($payload['key']));
        
        $payload['new_key'] = 'new_value';
        $this->assertEquals('new_value', $payload['new_key']);
        
        unset($payload['key']);
        $this->assertFalse(isset($payload['key']));
    }

    public function test_can_access_data_like_object()
    {
        $payload = ProcessPayload::make(['name' => 'Alice']);
        
        $this->assertEquals('Alice', $payload->name);
        
        $payload->age = 30;
        $this->assertEquals(30, $payload->age);
        
        $this->assertTrue(isset($payload->age));
    }

    public function test_can_merge_data()
    {
        $payload = ProcessPayload::make(['a' => 1, 'b' => 2]);
        $payload->merge(['b' => 3, 'c' => 4]);
        
        $this->assertEquals(1, $payload->get('a'));
        $this->assertEquals(3, $payload->get('b')); // overwritten
        $this->assertEquals(4, $payload->get('c'));
    }

    public function test_can_convert_to_array_and_json()
    {
        $data = ['user' => ['name' => 'Bob', 'email' => 'bob@example.com']];
        $payload = ProcessPayload::make($data);
        
        $this->assertEquals($data, $payload->toArray());
        
        $json = $payload->toJson();
        $decoded = json_decode($json, true);
        
        $this->assertEquals($data, $decoded['data']);
        $this->assertArrayHasKey('metadata', $decoded);
    }

    public function test_can_use_collection_helpers()
    {
        $payload = ProcessPayload::make(['users' => [
            ['name' => 'Alice', 'active' => true],
            ['name' => 'Bob', 'active' => false],
            ['name' => 'Charlie', 'active' => true],
        ]]);
        
        $activeUsers = $payload->collect()->get('users', []);
        $activeNames = collect($activeUsers)->where('active', true)->pluck('name');
        
        $this->assertEquals(['Alice', 'Charlie'], $activeNames->toArray());
    }

    public function test_payload_with_nested_data()
    {
        $payload = ProcessPayload::make([
            'user' => ['name' => 'Dave', 'profile' => ['age' => 25]]
        ]);
        
        $this->assertEquals('Dave', $payload->get('user.name'));
        $this->assertEquals(25, $payload->get('user.profile.age'));
        
        $payload->set('user.profile.city', 'Paris');
        $this->assertEquals('Paris', $payload->get('user.profile.city'));
    }

    public function test_payload_only_and_except()
    {
        $payload = ProcessPayload::make([
            'name' => 'Eva',
            'email' => 'eva@example.com',
            'password' => 'secret',
            'age' => 28
        ]);
        
        $public = $payload->only(['name', 'email', 'age']);
        $this->assertEquals(['name' => 'Eva', 'email' => 'eva@example.com', 'age' => 28], $public);
        
        $withoutSecret = $payload->except(['password']);
        $this->assertEquals(['name' => 'Eva', 'email' => 'eva@example.com', 'age' => 28], $withoutSecret);
    }
}
