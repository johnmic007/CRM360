<div>
    <form wire:submit.prevent="submit">
        <div>
            <x-filament::card>
                <x-filament::form>
                    <x-filament::form.field label="Message to Manager">
                        <textarea wire:model.defer="message" 
                                  class="w-full border-gray-300 rounded-md" 
                                  rows="5" 
                                  placeholder="Write your message to the manager here..."></textarea>
                        @error('message') <span class="text-red-600">{{ $message }}</span> @enderror
                    </x-filament::form.field>
                </x-filament::form>
            </x-filament::card>
        </div>
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" class="bg-primary-500 text-white">
                Send Request
            </x-filament::button>
        </div>
    </form>
</div>
